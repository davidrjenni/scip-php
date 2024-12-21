<?php

declare(strict_types=1);

namespace ScipPhp\Types\Internal;

use Override;

final class MixedIterableType implements IterableType
{
    /** @param  non-empty-array<int|string, Type>  $types */
    public function __construct(private readonly array $types)
    {
    }

    /** @inheritDoc */
    #[Override]
    public function flatten(): array
    {
        return [];
    }

    #[Override]
    public function valueType(int|string|null $key): ?Type
    {
        return $this->types[$key] ?? null;
    }
}
