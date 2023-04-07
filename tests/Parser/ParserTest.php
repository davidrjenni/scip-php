<?php

declare(strict_types=1);

namespace Tests\Parser;

use PhpParser\Node;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use ScipPhp\Parser\Parser;
use ScipPhp\Parser\PosResolver;

use const DIRECTORY_SEPARATOR;

final class ParserTest extends TestCase
{
    private Parser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new Parser();
    }

    public function testEmptyFile(): void
    {
        self::expectException(RuntimeException::class);

        $this->parser->traverse(
            __DIR__ . DIRECTORY_SEPARATOR . 'testdata' . DIRECTORY_SEPARATOR . 'empty.php',
            $this,
            function (PosResolver $pos, Node $n): void {
            },
        );
    }
}
