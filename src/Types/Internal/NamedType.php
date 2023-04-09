<?php

declare(strict_types=1);

namespace ScipPhp\Types\Internal;

final class NamedType implements Type
{
    /** @param  non-empty-string  $name */
    public function __construct(private readonly string $name)
    {
    }

    /** @inheritDoc */
    public function flatten(): array
    {
        return [$this->name];
    }
}
