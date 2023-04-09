<?php

declare(strict_types=1);

namespace ScipPhp\Types\Internal;

interface Type
{
    /** @return array<int, non-empty-string> */
    public function flatten(): array;
}
