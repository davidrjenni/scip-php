<?php

declare(strict_types=1);

namespace TestData;

/**
 * @property              $d3
 * @property-read  ClassB $d4
 * @property-write ClassA $d5
 * @property       array<int, array{
 *     ClassA,
 *     ClassB,
 * }>                     $d6
 */
final class ClassD extends ClassA
{

    public function __construct(
        public readonly ClassF $d1,
        public readonly int $d2,
    ) {
    }
}

final readonly class ClassJ
{
    public const J0 = 42;
}
