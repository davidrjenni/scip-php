<?php

declare(strict_types=1);

namespace TestData;

use function strlen;

final class ClassF
{
    public readonly int $f1;

    public EnumG $f2;

    private static ClassA $f3;

    public function f1(): int
    {
        return $this->f1 + 42 + strlen('ABC');
    }

    public static function f2(): ClassA
    {
        return self::$f3;
    }
}

namespace TestData3;

final class ClassJ
{
    public const J1 = 42;
}
