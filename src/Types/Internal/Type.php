<?php

declare(strict_types=1);

namespace ScipPhp\Types\Internal;

interface Type
{
    /** @return list<non-empty-string> */
    public function flatten(): array;
}
