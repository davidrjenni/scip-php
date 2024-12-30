<?php

declare(strict_types=1);

namespace Tests\Parser;

use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\PropertyItem;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ScipPhp\Parser\Parser;
use ScipPhp\Parser\PosResolver;

use function array_slice;
use function implode;

use const DIRECTORY_SEPARATOR;

final class PosResolverTest extends TestCase
{
    private Parser $parser;

    #[Override]
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
                if ($n instanceof PropertyItem && $n->name->toString() === 'p') {
                    self::assertSame([5, 18, 5, 20], $pos->pos($n->name));
                }
                if ($n instanceof PropertyFetch && $n->name instanceof Identifier && $n->name->toString() === 'p') {
                    self::assertSame([8, 11, 8, 12], $pos->pos($n->name));
                }
            },
        );
    }

    /**
     * @param  non-empty-string           $docText
     * @param  non-negative-int           $startLine
     * @param  non-empty-string           $tagName
     * @param  non-empty-string           $name
     * @param  array{int, int, int, int}  $pos
     */
    #[DataProvider('providePosInDoc')]
    public function testPosInDoc(string $docText, int $startLine, string $tagName, string $name, array $pos): void
    {
        $actual = PosResolver::posInDoc($docText, $startLine, $tagName, $name);

        self::assertSame($pos, $actual);
    }

    /**
     * @return array<non-empty-string, array{
     *     non-empty-string,
     *     non-negative-int,
     *     non-empty-string,
     *     non-empty-string,
     *     array{int, int, int, int},
     * }>
     */
    public static function providePosInDoc(): array
    {
        $multiline = [
            '/** @property int $p1',
            '  *  @property-read   ?Foo    $p2',
            '* @property-write Foo&Bar $p3 additional documentation',
            ' * @method Foo m1() additionl documentation',
            ' * @method Foo m2(int $p1, bool $p2, string $p3)',
            '* @property array<int, array{',
            '* id: int,',
            '* name: string,',
            '* }> $p3',
            '* @property int $p4 additional documentation line 1',
            '*                   additional documentation line 2',
            ' */',
        ];

        return [
            '@property-on-one-line' => [
                '/** @property int $foo */',
                0,
                '@property',
                '$foo',
                [0, 18, 0, 22],
            ],
            '@property-on-one-line-with-additional-documentation' => [
                '/** @property int $foo additional documentation */',
                0,
                '@property',
                '$foo',
                [0, 18, 0, 22],
            ],
            '@property-on-one-line-without-type' => [
                '/** @property $foo */',
                0,
                '@property',
                '$foo',
                [0, 14, 0, 18],
            ],
            '@method-on-one-line' => [
                '/** @method void foo() */',
                0,
                '@method',
                'foo',
                [0, 17, 0, 20],
            ],
            '@method-on-one-line-with-additional-documentation' => [
                '/** @method void foo() additional documentation */',
                0,
                '@method',
                'foo',
                [0, 17, 0, 20],
            ],
            '@property-inside-multiline-doc-comment' => [
                $multiline[0],
                0,
                '@property',
                '$p1',
                [0, 18, 0, 21],
            ],
            '@property-read-inside-multiline-doc-comment' => [
                $multiline[1],
                1,
                '@property',
                '$p2',
                [1, 30, 1, 33],
            ],
            '@property-write-inside-multiline-doc-comment' => [
                $multiline[2],
                2,
                '@property',
                '$p3',
                [2, 26, 2, 29],
            ],
            '@method-inside-multiline-doc-comment-1' => [
                $multiline[3],
                3,
                '@method',
                'm1',
                [3, 15, 3, 17],
            ],
            '@method-inside-multiline-doc-comment-2' => [
                $multiline[4],
                4,
                '@method',
                'm2',
                [4, 15, 4, 17],
            ],
            'multiline-@property-inside-multiline-doc-comment-1' => [
                implode("\n", array_slice($multiline, 5, 4)),
                5,
                '@property',
                '$p3',
                [8, 5, 8, 8],
            ],
            'multiline-@property-inside-multiline-doc-comment-2' => [
                implode("\n", array_slice($multiline, 9, 2)),
                9,
                '@property',
                '$p4',
                [9, 16, 9, 19],
            ],
        ];
    }
}
