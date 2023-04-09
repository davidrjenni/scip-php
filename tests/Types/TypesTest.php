<?php

declare(strict_types=1);

namespace Tests\Types;

use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;
use ScipPhp\Composer\Composer;
use ScipPhp\File\Reader;
use ScipPhp\SymbolNamer;
use ScipPhp\Types\Types;

use function str_ends_with;

use const DIRECTORY_SEPARATOR;

final class TypesTest extends TestCase
{
    private const TESTDATA_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'testdata' . DIRECTORY_SEPARATOR . 'scip-php-test';

    private Parser $parser;

    /** @var array<non-empty-string, array<int, Stmt>> */
    private array $stmts;

    private Types $types;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = $this->createParser();
        $this->stmts = [];

        $composer = new Composer(self::TESTDATA_DIR);
        $namer = new SymbolNamer($composer);

        $this->types = new Types($namer);
    }

    public function testNameDefs(): void
    {
        $this->assertName('ClassA.php', 'ClassB', 9, 'TestData/ClassB#');
        $this->assertName('ClassA.php', 'null', 24, null);
        $this->assertName('ClassA.php', 'ClassB', 28, 'TestData/ClassB#');
        $this->assertName('ClassA.php', 'EnumG', 35, 'TestData/EnumG#');
        $this->assertName('ClassA.php', 'ClassF', 43, 'TestData/ClassF#');
        $this->assertName('ClassA.php', 'ClassF', 44, 'TestData/ClassF#');
        $this->assertName('ClassA.php', 'ClassF', 49, 'TestData/ClassF#');
        $this->assertName('ClassA.php', 'ClassF', 50, 'TestData/ClassF#');
        $this->assertName('ClassA.php', 'PHP_MAJOR_VERSION', 60, 'PHP_MAJOR_VERSION.');

        $this->assertName('ClassB.php', 'ClassC', 11, 'TestData/ClassC#');
        $this->assertName('ClassB.php', 'ClassD', 15, 'TestData/ClassD#');
        $this->assertName('ClassB.php', 'ClassF', 15, 'TestData/ClassF#');
        $this->assertName('ClassB.php', 'self', 17, 'TestData/ClassB#');

        $this->assertName('ClassC.php', 'TraitE', 9, 'TestData/TraitE#');
        $this->assertName('ClassC.php', 'ClassB', 13, 'TestData/ClassB#');
        $this->assertName('ClassC.php', 'ClassD', 13, 'TestData/ClassD#');
        $this->assertName('ClassC.php', 'ClassB', 15, 'TestData/ClassB#');
        $this->assertName('ClassC.php', 'ClassD', 15, 'TestData/ClassD#');

        $this->assertName('ClassD.php', 'ClassA', 7, 'TestData/ClassA#');
        $this->assertName('ClassD.php', 'ClassF', 11, 'TestData/ClassF#');

        $this->assertName('ClassF.php', 'strlen', 7, 'strlen().');
        $this->assertName('ClassF.php', 'EnumG', 13, 'TestData/EnumG#');
        $this->assertName('ClassF.php', 'ClassA', 15, 'TestData/ClassA#');
        $this->assertName('ClassF.php', 'ClassA', 22, 'TestData/ClassA#');
        $this->assertName('ClassF.php', 'strlen', 19, 'strlen().');
        $this->assertName('ClassF.php', 'self', 24, 'TestData/ClassF#');

        $this->assertName('ClassH.php', 'Exception', 7, 'Exception#');
        $this->assertName('ClassH.php', 'Exception', 9, 'Exception#');
        $this->assertName('ClassH.php', 'parent', 14, 'Exception#');

        $this->assertName('TraitE.php', 'ClassI', 7, 'Test/Dep/ClassI#');
        $this->assertName('TraitE.php', 'ClassI', 13, 'Test/Dep/ClassI#');
        $this->assertName('TraitE.php', 'ClassI', 22, 'Test/Dep/ClassI#');
        $this->assertName('TraitE.php', 'count', 29, 'count().');
        $this->assertName('TraitE.php', 'true', 28, null);
        $this->assertName('TraitE.php', 'false', 31, null);
    }

    /**
     * @param  non-empty-string  $filename
     * @param  non-empty-string  $name
     * @param  ?non-empty-string  $def
     */
    private function assertName(string $filename, string $name, int $line, ?string $def): void
    {
        $x = $this->findNode(
            $filename,
            $line,
            fn(Node $n): bool => $n instanceof Name && str_ends_with($n->toString(), $name),
        );

        self::assertInstanceOf(Name::class, $x);

        $d = $this->types->nameDef($x);
        if ($def === null) {
            self::assertNull($d);
            return;
        }
        self::assertNotNull($d);
        self::assertStringEndsWith($def, $d);
    }

    /**
     * @param  non-empty-string  $filename
     * @param  callable(Node): bool  $filter
     */
    private function findNode(string $filename, int $line, callable $filter): Node
    {
        if (!isset($this->stmts[$filename])) {
            $filename = self::TESTDATA_DIR . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $filename;
            $code = Reader::read($filename);
            $stmts = $this->parser->parse($code);
            self::assertNotNull($stmts);
            $this->stmts[$filename] = $stmts;
        }

        $finder = new FindingVisitor(fn(Node $n): bool => $filter($n) && $n->getStartLine() === $line);
        $t = new NodeTraverser();
        $t->addVisitor(new NameResolver());
        $t->addVisitor(new ParentConnectingVisitor());
        $t->addVisitor($finder);
        $t->traverse($this->stmts[$filename]);
        $n = $finder->getFoundNodes()[0] ?? null;

        self::assertNotNull($n);
        return $n;
    }

    private function createParser(): Parser
    {
        return (new ParserFactory())->create(
            ParserFactory::ONLY_PHP7,
            new Lexer(
                [
                    'usedAttributes' => [
                        'comments',
                        'startLine',
                        'endLine',
                        'startTokenPos',
                        'endTokenPos',
                        'startFilePos',
                        'endFilePos',
                    ],
                ],
            ),
        );
    }
}
