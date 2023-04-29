<?php

declare(strict_types=1);

namespace Tests\Types\Internal;

use PHPUnit\Framework\TestCase;
use ScipPhp\Types\Internal\NamedType;

final class NamedTypeTest extends TestCase
{
    public function testFlatten(): void
    {
        $name = 'scip-php composer php 8.2.4 Exception#';
        $t = new NamedType($name);

        self::assertSame([$name], $t->flatten());
    }
}
