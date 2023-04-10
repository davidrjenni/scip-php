<?php

declare(strict_types=1);

namespace TestData;

final class ClassB
{
    public const B1 = 42;

    public static ?ClassC $b1;

    public int $b2;

    public function b1(int $p1, ClassD|ClassF $p2): int
    {
        return self::$b1->c2->d2 * $p1;
    }

    public function b2(): void
    {
        \TestData\ClassJ::J0;
        \TestData3\ClassJ::J1;
        \TestData2\ClassJ::J2;
        \ClassJ::J3;
    }
}
