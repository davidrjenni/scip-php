<?php

declare(strict_types=1);

namespace ScipPhp\Types\Internal;

use Override;

final readonly class UniformIterableType implements IterableType
{
    public function __construct(private Type $type)
    {
    }

    /** @inheritDoc */
    #[Override]
    public function flatten(): array
    {
        return [];
    }

    #[Override]
    public function valueType(int|string|null $key): ?Type // phpcs:ignore
    {
        return $this->type;
    }
}
