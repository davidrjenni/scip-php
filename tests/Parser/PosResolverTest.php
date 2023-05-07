<?php

declare(strict_types=1);

namespace Tests\Parser;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\PropertyProperty;
use PHPUnit\Framework\TestCase;
use ScipPhp\Parser\Parser;
use ScipPhp\Parser\PosResolver;

use const DIRECTORY_SEPARATOR;

final class PosResolverTest extends TestCase
{
    private Parser $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parser = new Parser();
    }

    public function testPos(): void
    {
        $this->parser->traverse(
            __DIR__ . DIRECTORY_SEPARATOR . 'testdata' . DIRECTORY_SEPARATOR . 'test.php',
            $this,
            function (PosResolver $pos, Node $n): void { // phpcs:ignore
                if ($n instanceof Variable && $n->name === 'x') {
                    self::assertSame([2, 0, 2, 2], $pos->pos($n));
                }
                if ($n instanceof Variable && $n->name === 'yy') {
                    self::assertSame([3, 0, 3, 3], $pos->pos($n));
                }
                if ($n instanceof New_) {
                    self::assertSame([4, 7, 6, 1], $pos->pos($n));
                }
                if ($n instanceof PropertyProperty && $n->name->toString() === 'p') {
                    self::assertSame([5, 18, 5, 20], $pos->pos($n->name));
                }
                if ($n instanceof PropertyFetch && $n->name instanceof Identifier && $n->name->toString() === 'p') {
                    self::assertSame([8, 11, 8, 12], $pos->pos($n->name));
                }
            },
        );
    }
}
