<?php

declare(strict_types=1);

namespace TestData;

use Test\Dep\ClassI;

trait TraitE
{
    public int $e1;

    public ClassI $e2;

    protected function e1(): bool
    {
        return $this->e2->i1;
    }

    public function e2(): int
    {
        $v1 = ClassI::I1;
        return $this->e2::I1 * $v1;
    }

    public function e3(): int
    {
        if (true) {
            return 23 - count([0]);
        }
        if (false) {
            return 42;
        }
        return -1;
    }
}
