<?php

declare(strict_types=1);

namespace Tests\Types\Internal;

use PHPUnit\Framework\TestCase;
use ScipPhp\Types\Internal\CompositeType;
use ScipPhp\Types\Internal\NamedType;

final class CompositeTypeTest extends TestCase
{
    public function testFlatten(): void
    {
        $name1 = 'scip-php composer php 8.2.4 Exception#';
        $name2 = 'scip-php composer php 8.2.4 RuntimeException#';
        $name3 = 'scip-php composer php 8.2.4 LogicException#';
        $name4 = 'scip-php composer php 8.2.4 OverflowException#';
        $t1 = new NamedType($name1);
        $t2 = new NamedType($name2);
        $t3 = new NamedType($name3);
        $t4 = new NamedType($name4);

        $t5 = new CompositeType($t1, $t2, null);
        $t6 = new CompositeType($t3, $t4, null);

        $t = new CompositeType($t4, $t5, $t6, null);

        self::assertCount(4, $t->flatten());
        self::assertEqualsCanonicalizing([$name1, $name2, $name3, $name4], $t->flatten());
    }
}
