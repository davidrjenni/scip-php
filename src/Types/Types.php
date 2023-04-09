<?php

declare(strict_types=1);

namespace ScipPhp\Types;

use PhpParser\Node\Name;
use ScipPhp\SymbolNamer;
use ScipPhp\Types\Internal\NamedType;
use ScipPhp\Types\Internal\Type;

use function in_array;
use function strtolower;

final class Types
{
    public function __construct(
        private readonly SymbolNamer $namer,
    ) {
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

    private function type(Name $x): ?Type
    {
        $n = $this->namer->name($x);
        if ($n === null) {
            return null;
        }
        return new NamedType($n);
    }
}
