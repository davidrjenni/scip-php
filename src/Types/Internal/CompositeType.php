<?php

declare(strict_types=1);

namespace ScipPhp\Types\Internal;

use function array_keys;

final class CompositeType implements Type
{
    /** @var array<int, non-empty-string> */
    private readonly array $types;

    public function __construct(?Type ...$types)
    {
        $flattened = [];
        foreach ($types as $type) {
            if ($type === null) {
                continue;
            }
            foreach ($type->flatten() as $t) {
                $flattened[$t] = true;
            }
        }
        $this->types = array_keys($flattened);
    }

    /** @inheritDoc */
    public function flatten(): array
    {
        return $this->types;
    }
}
