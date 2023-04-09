<?php

declare(strict_types=1);

namespace ScipPhp\Types\Internal;

use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType;
use ScipPhp\SymbolNamer;

final class TypeParser
{
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
}
