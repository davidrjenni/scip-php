<?php

declare(strict_types=1);

namespace ScipPhp\Types\Internal;

final class UniformIterableType implements IterableType
{
    public function __construct(private readonly Type $type)
    {
    }

    /** @inheritDoc */
    public function flatten(): array
    {
        return [];
    }

    public function valueType(int|string|null $key): ?Type // phpcs:ignore
    {
        return $this->type;
    }
}
