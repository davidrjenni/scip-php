<?php

declare(strict_types=1);

namespace ScipPhp\Types\Internal;

interface IterableType extends Type
{
    public function valueType(int|string|null $key): ?Type;
}
