<?php

declare(strict_types=1);

namespace TestData5;

use TestData\ClassA;
use TestData\ClassB;
use TestData\ClassC;
use TestData\ClassH;

/**
 * @property       ClassA $k1
 * @property-read  ?ClassB $k2
 * @property-write ClassC $k3
 * @property       ClassA&ClassB $k4
 * @property       ClassA|ClassC $k5
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
        $this->k4->a1();
        $this->k4->b2();
        $this->k5->a1();
        $this->k5->e1();
        $this->k3()[0]->a1();
        $this->k4()[0][0]->a1();
    }

    /** @return ClassA[] */
    public function k3(): array
    {
        return [new ClassA()];
    }

    /** @return ClassA[][] */
    public function k4(): array
    {
        return [[new ClassA()]];
    }
}
