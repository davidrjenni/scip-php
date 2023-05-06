<?php

declare(strict_types=1);

namespace TestData5;

use TestData\ClassA;
use TestData\ClassB;
use TestData\ClassC;
use TestData\ClassH;

/**
 * @property       ClassA $k1
 * @property-read  ClassB $k2
 * @property-write ClassC $k3
 *
 * @method ClassH k1()
 */
final class ClassK
{
    public const K1 = 123;

    public function k2(): void
    {
        $this->k1->a1();
        $this->k2->b1();
        $this->k3->c1()->d1->f1();
        $this->k1()->h1();
    }
}
