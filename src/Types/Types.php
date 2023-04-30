<?php

declare(strict_types=1);

namespace ScipPhp\Types;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\Clone_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\NullsafePropertyFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\EnumCase;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use ScipPhp\Composer\Composer;
use ScipPhp\Parser\Parser;
use ScipPhp\Parser\PosResolver;
use ScipPhp\SymbolNamer;
use ScipPhp\Types\Internal\CompositeType;
use ScipPhp\Types\Internal\NamedType;
use ScipPhp\Types\Internal\Type;
use ScipPhp\Types\Internal\TypeParser;

use function array_key_exists;
use function in_array;
use function is_string;
use function strtolower;

final class Types
{
    private readonly Parser $parser;

    private readonly TypeParser $typeParser;

    /** @var array<non-empty-string, array<int, non-empty-string>> */
    private array $uppers;

    /** @var array<non-empty-string, ?Type> */
    private array $defs;

    /** @var array<non-empty-string, true> */
    private array $seenDepFiles;

    public function __construct(
        private readonly Composer $composer,
        private readonly SymbolNamer $namer,
    ) {
        $this->parser = new Parser();
        $this->typeParser = new TypeParser($namer);
        $this->uppers = [];
        $this->defs = [];
        $this->seenDepFiles = [];
    }

    /** @return ?non-empty-string */
    public function nameDef(Name $n): ?string
    {
        if (in_array(strtolower($n->toString()), ['null', 'true', 'false'], true)) {
            return null;
        }
        $type = $this->type($n);
        return $type?->flatten()[0] ?? null;
    }

    /**
     * @param  non-empty-string  $const
     * @return ?non-empty-string
     */
    public function constDef(Expr|Name $x, string $const): ?string
    {
        if ($const === 'class') {
            return null;
        }
        $type = $this->type($x);
        if ($type === null) {
            return null;
        }
        return $this->findDef(
            $x,
            $type->flatten(),
            fn(string $t): string => $this->namer->nameConst($t, $const),
        );
    }

    /**
     * @param  non-empty-string  $prop
     * @return ?non-empty-string
     */
    public function propDef(Expr|Name $x, string $prop): ?string
    {
        $type = $this->type($x);
        if ($type === null) {
            return null;
        }
        return $this->findDef(
            $x,
            $type->flatten(),
            fn(string $t): string => $this->namer->nameProp($t, $prop),
        );
    }

    /**
     * @param  non-empty-string  $meth
     * @return ?non-empty-string
     */
    public function methDef(Expr|Name $x, string $meth): ?string
    {
        $type = $this->type($x);
        if ($type === null) {
            return null;
        }
        return $this->findDef(
            $x,
            $type->flatten(),
            fn(string $t): string => $this->namer->nameMeth($t, $meth),
        );
    }

    private function type(Expr|Name $x): ?Type
    {
        if ($x instanceof Assign) {
            return $this->type($x->expr);
        }

        if ($x instanceof BinaryOp && $x->getOperatorSigil() === '??') {
            $leftType = $this->type($x->left);
            $rightType = $this->type($x->right);
            return new CompositeType($leftType, $rightType);
        }

        if ($x instanceof Clone_) {
            return $this->type($x->expr);
        }

        if ($x instanceof FuncCall && $x->name instanceof Name && $x->name->toString() !== '') {
            $name = $this->namer->name($x->name);
            if ($name === null) {
                return null;
            }
            return $this->defs[$name] ?? null;
        }

        if ($x instanceof Match_) {
            $types = [];
            foreach ($x->arms as $a) {
                $types[] = $this->type($a->body);
            }
            return new CompositeType(...$types);
        }

        if (
            ($x instanceof MethodCall || $x instanceof NullsafeMethodCall || $x instanceof StaticCall)
            && $x->name instanceof Identifier
            && $x->name->toString() !== ''
        ) {
            $type = $x instanceof StaticCall
                ? $this->type($x->class)
                : $this->type($x->var);
            return $this->findDefType(
                $type,
                $x,
                fn(string $t): string => $this->namer->nameMeth($t, $x->name->toString()),
            );
        }

        if ($x instanceof New_) {
            if ($x->class instanceof Class_) {
                $name = $this->namer->name($x->class);
                if ($name === null) {
                    return null;
                }
                return new NamedType($name);
            }
            return $this->type($x->class);
        }

        if ($x instanceof Name) {
            $n = $this->namer->name($x);
            if ($n === null) {
                return null;
            }
            return new NamedType($n);
        }

        if (
            ($x instanceof PropertyFetch || $x instanceof NullsafePropertyFetch || $x instanceof StaticPropertyFetch)
            && $x->name instanceof Identifier
            && $x->name->toString() !== ''
        ) {
            $type = $x instanceof StaticPropertyFetch
                ? $this->type($x->class)
                : $this->type($x->var);
            return $this->findDefType(
                $type,
                $x,
                fn(string $t): string => $this->namer->nameProp($t, $x->name->toString()),
            );
        }

        if ($x instanceof Ternary) {
            $elseType = $this->type($x->else);
            if ($x->if !== null) {
                $ifType = $this->type($x->if);
                return new CompositeType($ifType, $elseType);
            }
            $condType = $this->type($x->cond);
            return new CompositeType($condType, $elseType);
        }

        if ($x instanceof Variable) {
            if ($x->name === 'this') {
                $name = $this->namer->nameNearestClassLike($x);
                if ($name === null) {
                    return null;
                }
                return new NamedType($name);
            }
        }

        return null;
    }

    /** @param  callable(non-empty-string): non-empty-string  $name */
    private function findDefType(?Type $t, Expr|Name $x, callable $name): ?Type
    {
        if ($t === null) {
            return null;
        }
        $name = $this->findDef($x, $t->flatten(), $name);
        return $this->defs[$name] ?? null;
    }

    /**
     * @param  array<int, non-empty-string>  $types
     * @param  callable(non-empty-string): non-empty-string  $name
     * @return ?non-empty-string
     */
    private function findDef(Expr|Name $x, array $types, callable $name): ?string
    {
        foreach ($types as $t) {
            $c = $name($t);
            if (array_key_exists($c, $this->defs)) {
                return $c;
            }
        }
        foreach ($types as $t) {
            $uppers = $this->uppers[$t] ?? [];
            $c = $this->findDef($x, $uppers, $name);
            if ($c !== null) {
                return $c;
            }
        }
        foreach ($types as $t) {
            $ident = $this->namer->extractIdent($t);
            if (!$this->composer->isDependency($ident)) {
                continue;
            }
            $f = $this->composer->findFile($ident);
            if ($f === null) {
                continue;
            }
            if (isset($this->seenDepFiles[$f])) {
                return null;
            }
            $this->parser->traverse($f, $this, $this->collectDefs(...));
            $this->seenDepFiles[$f] = true;
            $type = $this->type($x);
            if ($type !== null) {
                return $this->findDef($x, $type->flatten(), $name);
            }
        }
        return null;
    }

    /** @param  non-empty-string  $filenames */
    public function collect(string ...$filenames): void
    {
        foreach ($filenames as $f) {
            $this->parser->traverse($f, $this, $this->collectDefs(...));
        }
    }

    private function collectDefs(PosResolver $pos, Node $n): void
    {
        if ($n instanceof ClassConst) {
            foreach ($n->consts as $c) {
                $name = $this->namer->name($c);
                if ($name !== null) {
                    $this->defs[$name] = null;
                }
            }
        } elseif ($n instanceof ClassLike) {
            $this->collectUppers($n);
            $name = $this->namer->name($n);
            if ($name !== null) {
                $this->defs[$name] = new NamedType($name);
            }
        } elseif ($n instanceof EnumCase) {
            $name = $this->namer->name($n);
            if ($name !== null) {
                $this->defs[$name] = null;
            }
        } elseif ($n instanceof FunctionLike) {
            $name = $this->namer->name($n);
            if ($name !== null) {
                $type = $this->typeParser->parse($n->getReturnType());
                $this->defs[$name] = $type;
            }
        } elseif ($n instanceof Param && $n->var instanceof Variable && is_string($n->var->name)) {
            // Constructor property promotion.
            if ($n->flags !== 0) {
                $p = new PropertyProperty($n->var->name, $n->default, $n->getAttributes());
                $name = $this->namer->name($p);
                if ($name !== null) {
                    $type = $this->typeParser->parse($n->type);
                    $this->defs[$name] = $type;
                }
            }
        } elseif ($n instanceof Property) {
            foreach ($n->props as $p) {
                $name = $this->namer->name($p);
                if ($name !== null) {
                    $type = $this->typeParser->parse($n->type);
                    $this->defs[$name] = $type;
                }
            }
        }
    }

    private function collectUppers(ClassLike $c): void
    {
        $name = $this->namer->name($c);
        if ($name === null) {
            return;
        }

        foreach ($c->getTraitUses() as $use) {
            foreach ($use->traits as $t) {
                $this->addUpper($name, $t);
            }
        }
        if ($c instanceof Class_) {
            if ($c->extends !== null) {
                $this->addUpper($name, $c->extends);
            }
            foreach ($c->implements as $i) {
                $this->addUpper($name, $i);
            }
        } elseif ($c instanceof Interface_) {
            foreach ($c->extends as $i) {
                $this->addUpper($name, $i);
            }
        }
    }

    /** @param  non-empty-string  $c */
    private function addUpper(string $c, Name $upper): void
    {
        $name = $this->namer->name($upper);
        if ($name === null) {
            return;
        }
        if (!isset($this->uppers[$c])) {
            $this->uppers[$c] = [];
        }
        $this->uppers[$c][] = $name;
        $this->defs[$name] = new NamedType($name);
    }
}
