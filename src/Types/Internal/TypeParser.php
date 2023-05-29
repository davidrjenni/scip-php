<?php

declare(strict_types=1);

namespace ScipPhp\Types\Internal;

use PhpParser\ErrorHandler\Throwing as ThrowingErrorHandler;
use PhpParser\NameContext;
use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\UnionType;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprStringNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\ConditionalTypeForParameterNode;
use PHPStan\PhpDocParser\Ast\Type\ConditionalTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\ThisTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use ScipPhp\SymbolNamer;

use function count;
use function in_array;
use function ltrim;
use function str_starts_with;

final class TypeParser
{
    private const BUILTIN_TYPES = [
        'void',
        'null',
        'true',
        'false',
        'never',
        'never-return',
        'never-returns',
        'no-return',
        'bool',
        'boolean',
        'int',
        'integer',
        'positive-int',
        'negative-int',
        'float',
        'double',
        'string',
        'callable-string',
        'numeric-string',
        'non-empty-string',
        'non-falsy-string',
        'literal-string',
        'scalar',
        'mixed',
        'array',
        'non-empty-array',
        'non-empty-list',
        'callable',
        'object',
        'iterable',
        'resource',
    ];

    public function __construct(private readonly SymbolNamer $namer)
    {
    }

    public function parse(ComplexType|Name|Identifier|null $n): ?Type
    {
        if ($n instanceof FullyQualified) {
            $name = $this->namer->name($n);
            if ($name === null) {
                return null;
            }
            return new NamedType($name);
        }
        if ($n instanceof NullableType) {
            return $this->parse($n->type);
        }
        if ($n instanceof UnionType || $n instanceof IntersectionType) {
            $types = [];
            foreach ($n->types as $t) {
                $types[] = $this->parse($t);
            }
            return new CompositeType(...$types);
        }
        return null;
    }

    public function parseDoc(Node $node, ?TypeNode $type): ?Type
    {
        if ($type instanceof ArrayShapeNode) {
            $types = [];
            foreach ($type->items as $i => $item) {
                $t = $this->parseDoc($node, $item->valueType);
                if ($t === null) {
                    continue;
                }
                $key = $i;
                if ($item->keyName instanceof ConstExprIntegerNode || $item->keyName instanceof ConstExprStringNode) {
                    $key = $item->keyName->value;
                } elseif ($item->keyName instanceof IdentifierTypeNode) {
                    $key = $item->keyName->name;
                }
                $types[$key] = $t;
            }
            if (count($types) > 0) {
                return new MixedIterableType($types);
            }
        }

        if ($type instanceof ArrayTypeNode) {
            $t = $this->parseDoc($node, $type->type);
            if ($t !== null) {
                return new UniformIterableType($t);
            }
        }

        if ($type instanceof ConditionalTypeNode || $type instanceof ConditionalTypeForParameterNode) {
            $ifType = $this->parseDoc($node, $type->if);
            $elseType = $this->parseDoc($node, $type->else);
            return new CompositeType($ifType, $elseType);
        }

        if ($type instanceof IdentifierTypeNode) {
            if (in_array($type->name, self::BUILTIN_TYPES, true)) {
                return null;
            }

            $name = str_starts_with($type->name, '\\')
                ? new FullyQualified(ltrim($type->name, '\\'), ['parent' => $node])
                : new Name($type->name, ['parent' => $node]);

            if ($name->isSpecialClassName()) {
                $n = $this->namer->name($name);
                if ($n === null) {
                    return null;
                }
                return new NamedType($n);
            }

            $name = self::resolveName($node, $name);
            if ($name === null) {
                return null;
            }

            $n = $this->namer->name($name);
            if ($n === null) {
                return null;
            }
            return new NamedType($n);
        }

        if ($type instanceof IntersectionTypeNode || $type instanceof UnionTypeNode) {
            $types = [];
            foreach ($type->types as $t) {
                $types[] = $this->parseDoc($node, $t);
            }
            return new CompositeType(...$types);
        }

        if ($type instanceof NullableTypeNode) {
            return $this->parseDoc($node, $type->type);
        }

        if ($type instanceof ThisTypeNode) {
            $classLike = self::nearestClassLike($node);
            if ($classLike === null) {
                return null;
            }
            $name = $this->namer->name($classLike);
            if ($name === null) {
                return null;
            }
            return new NamedType($name);
        }

        return null;
    }

    private static function resolveName(Node $n, Name $name): ?Name
    {
        $ns = self::nearestNamespace($n);
        if ($ns === null) {
            // TODO(drj): make this work in files without a namespace.
            return null;
        }

        $nameCtx = new NameContext(new ThrowingErrorHandler());
        $nameCtx->startNamespace($ns->name);
        foreach ($ns->stmts as $stmt) {
            if ($stmt instanceof Use_) {
                foreach ($stmt->uses as $use) {
                    $type = $stmt->type | $use->type;
                    $nameCtx->addAlias($use->name, $use->getAlias()->name, $type, $use->getAttributes());
                }
            } elseif ($stmt instanceof GroupUse) {
                foreach ($stmt->uses as $use) {
                    $origName = Name::concat($stmt->prefix, $use->name);
                    if ($origName === null) {
                        continue;
                    }
                    $type = $stmt->type | $use->type;
                    $nameCtx->addAlias($origName, $use->getAlias()->name, $type, $use->getAttributes());
                }
            }
        }
        return $nameCtx->getResolvedName($name, Use_::TYPE_NORMAL);
    }

    private static function nearestClassLike(Node $n): ?ClassLike
    {
        while (true) {
            if ($n instanceof ClassLike) {
                return $n;
            }
            $n = $n->getAttribute('parent');
            if ($n === null) {
                return null;
            }
        }
    }

    private static function nearestNamespace(Node $n): ?Namespace_
    {
        while (true) {
            $n = $n->getAttribute('parent');
            if ($n === null) {
                return null;
            }
            if ($n instanceof Namespace_) {
                return $n;
            }
        }
    }
}
