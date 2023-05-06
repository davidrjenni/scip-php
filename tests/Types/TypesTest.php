<?php

declare(strict_types=1);

namespace Tests\Types;

use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\NullsafePropertyFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Identifier;
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

use function in_array;
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

        $this->types = new Types($composer, $namer);
        $this->types->collect(...$composer->projectFiles());
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
        $this->assertName('ClassH.php', 'fun2', 19, 'TestData4/fun2().');
        $this->assertName('ClassH.php', 'fun2', 20, 'fun2().');

        $this->assertName('TraitE.php', 'ClassI', 7, 'Test/Dep/ClassI#');
        $this->assertName('TraitE.php', 'ClassJ', 8, 'Test/Dep2/ClassJ#');
        $this->assertName('TraitE.php', 'ClassK', 9, 'TestData5/ClassK#');
        $this->assertName('TraitE.php', 'ClassI', 25, 'Test/Dep/ClassI#');
        $this->assertName('TraitE.php', 'ClassJ', 26, 'Test/Dep2/ClassJ#');
        $this->assertName('TraitE.php', 'ClassK', 26, 'TestData5/ClassK#');
        $this->assertName('TraitE.php', 'count', 32, 'count().');
        $this->assertName('TraitE.php', 'true', 31, null);
        $this->assertName('TraitE.php', 'false', 34, null);
    }

    public function testConstDefs(): void
    {
        $this->assertConstFetch('ClassA.php', 'B1', 28, 'TestData/ClassB#B1.');
        $this->assertConstFetch('ClassA.php', 'B1', 29, 'TestData/ClassB#B1.');
        $this->assertConstFetch('ClassA.php', 'I1', 25, 'Test/Dep/ClassI#I1.');
        $this->assertConstFetch('ClassA.php', 'G1', 34, 'TestData/EnumG#G1.');
        $this->assertConstFetch('ClassA.php', 'G2', 35, 'TestData/EnumG#G2.');
        $this->assertConstFetch('TraitE.php', 'I1', 25, 'Test/Dep/ClassI#I1.');
        $this->assertConstFetch('TraitE.php', 'I1', 26, 'Test/Dep/ClassI#I1.');
        $this->assertConstFetch('TraitE.php', 'J1', 26, 'Test/Dep2/ClassJ#J1.');
        $this->assertConstFetch('TraitE.php', 'K1', 26, 'TestData5/ClassK#K1.');

        $this->assertConstFetch('ClassB.php', 'J0', 22, 'TestData/ClassJ#J0.');
        $this->assertConstFetch('ClassB.php', 'J1', 23, 'TestData3/ClassJ#J1.');
        $this->assertConstFetch('ClassB.php', 'J2', 24, 'TestData2/ClassJ#J2.');
        $this->assertConstFetch('ClassB.php', 'J3', 25, 'ClassJ#J3.');
    }

    public function testMethDefs(): void
    {
        $this->assertMethCall('ClassA.php', 'a1', 23, 'TestData/ClassA#a1().');
        $this->assertMethCall('ClassA.php', 'i1', 24, 'Test/Dep/ClassI#i1().');
        $this->assertMethCall('ClassA.php', 'b1', 25, 'TestData/ClassB#b1().');
        $this->assertMethCall('ClassA.php', 'c1', 27, 'TestData/ClassC#c1().');
        $this->assertMethCall('ClassA.php', 'f1', 27, 'TestData/ClassF#f1().');
        $this->assertMethCall('ClassA.php', 'c1', 42, 'TestData/ClassC#c1().');
        $this->assertMethCall('ClassA.php', 'a1', 43, 'TestData/ClassA#a1().');
        $this->assertMethCall('ClassA.php', 'f2', 43, 'TestData/ClassF#f2().');
        $this->assertMethCall('ClassA.php', 'a1', 44, 'TestData/ClassA#a1().');

        $this->assertMethCall('ClassK.php', 'a1', 25, 'TestData/ClassA#a1().');
        $this->assertMethCall('ClassK.php', 'b1', 26, 'TestData/ClassB#b1().');
        $this->assertMethCall('ClassK.php', 'c1', 27, 'TestData/ClassC#c1().');
        $this->assertMethCall('ClassK.php', 'f1', 27, 'TestData/ClassF#f1().');
        $this->assertMethCall('ClassK.php', 'h1', 28, 'TestData/ClassH#h1().');

        $this->assertMethCall('ClassH.php', '__construct', 14, 'Exception#__construct().');
        $this->assertMethCall('ClassH.php', 'f2', 19, 'ClassF#f2().');
        $this->assertMethCall('ClassH.php', 'a2', 19, 'ClassA#a2().');
        $this->assertMethCall('ClassH.php', 'getCode', 20, 'Exception#getCode().');
        $this->assertMethCall('ClassH.php', 'a2', 20, 'ClassA#a2().');
    }

    public function testPropDefs(): void
    {
        $this->assertPropFetch('ClassA.php', 'a1', 15, 'TestData/ClassA#$a1.');
        $this->assertPropFetch('ClassA.php', 'b1', 15, 'TestData/ClassB#$b1.');
        $this->assertPropFetch('ClassA.php', 'c2', 15, 'TestData/ClassC#$c2.');
        $this->assertPropFetch('ClassA.php', 'b2', 15, 'TestData/ClassB#$b2.');
        $this->assertPropFetch('ClassA.php', 'e1', 16, 'TestData/TraitE#$e1.');
        $this->assertPropFetch('ClassA.php', 'd1', 17, 'TestData/ClassD#$d1.');
        $this->assertPropFetch('ClassA.php', 'f1', 17, 'TestData/ClassF#$f1.');
        $this->assertPropFetch('ClassA.php', 'a1', 18, 'TestData/ClassA#$a1.');
        $this->assertPropFetch('ClassA.php', 'b1', 18, 'TestData/ClassB#$b1.');
        $this->assertPropFetch('ClassA.php', 'c2', 18, 'TestData/ClassC#$c2.');
        $this->assertPropFetch('ClassA.php', 'a2', 18, 'TestData/ClassA#$a2.');
        $this->assertPropFetch('ClassA.php', 'i1', 24, 'Test/Dep/ClassI#$i1.');
        $this->assertPropFetch('ClassA.php', 'c1', 41, 'TestData/ClassC#$c1.');
        $this->assertPropFetch('ClassA.php', 'z1', 47, 'TestData/anon-class-447#$z1.');
        $this->assertPropFetch('ClassA.php', 'z1', 45, 'TestData/anon-class-447#$z1.');
        $this->assertPropFetch('ClassA.php', 'f1', 49, 'TestData/ClassF#$f1.');
        $this->assertPropFetch('ClassA.php', 'f1', 50, 'TestData/ClassF#$f1.');
        $this->assertPropFetch('ClassA.php', 'b1', 51, 'TestData/ClassB#$b1.');
        $this->assertPropFetch('ClassA.php', 'b1', 52, 'TestData/ClassB#$b1.');
        $this->assertPropFetch('ClassA.php', 'c1', 53, 'TestData/ClassC#$c1.');
        $this->assertPropFetch('ClassA.php', 'c1', 54, 'TestData/ClassC#$c1.');
        $this->assertPropFetch('ClassA.php', 'b2', 58, 'TestData/ClassB#$b2.');
        $this->assertPropFetch('ClassA.php', 'c1', 62, 'TestData/ClassC#$c1.');

        $this->assertPropFetch('ClassB.php', 'b1', 17, 'TestData/ClassB#$b1.');
        $this->assertPropFetch('ClassB.php', 'c2', 17, 'TestData/ClassC#$c2.');
        $this->assertPropFetch('ClassB.php', 'd2', 17, 'TestData/ClassD#$d2.');

        $this->assertPropFetch('ClassK.php', 'd1', 27, 'TestData/ClassD#$d1.');

        $this->assertPropFetch('TraitE.php', 'e2', 20, 'TestData/TraitE#$e2.');
        $this->assertPropFetch('TraitE.php', 'i1', 20, 'Test/Dep/ClassI#$i1.');
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
     * @param  non-empty-string  $name
     * @param  non-empty-string  $def
     */
    private function assertConstFetch(string $filename, string $name, int $line, string $def): void
    {
        $x = $this->findNode(
            $filename,
            $line,
            fn(Node $n): bool => $n instanceof ClassConstFetch
                && $n->name instanceof Identifier
                && $n->name->toString() === $name,
        );

        self::assertInstanceOf(ClassConstFetch::class, $x);
        self::assertInstanceOf(Identifier::class, $x->name);

        $d = $this->types->constDef($x->class, $name);
        self::assertNotNull($d);
        self::assertStringEndsWith($def, $d);
    }

    /**
     * @param  non-empty-string  $filename
     * @param  non-empty-string  $name
     * @param  non-empty-string  $def
     */
    private function assertMethCall(string $filename, string $name, int $line, string $def): void
    {
        $classes = [MethodCall::class, NullsafeMethodCall::class, StaticCall::class];
        $x = $this->findNode(
            $filename,
            $line,
            fn(Node $n): bool => in_array($n::class, $classes, true)
                && isset($n->name)
                && $n->name instanceof Identifier
                && $n->name->toString() === $name,
        );

        self::assertContains($x::class, $classes);
        self::assertTrue(isset($x->name));
        self::assertInstanceOf(Identifier::class, $x->name);

        if ($x instanceof StaticCall) {
            $d = $this->types->methDef($x->class, $name);
        } elseif ($x instanceof MethodCall || $x instanceof NullsafeMethodCall) {
            $d = $this->types->methDef($x->var, $name);
        } else {
            $class = $x::class;
            self::fail("Unexpected class: {$class}.");
        }

        self::assertNotNull($d);
        self::assertStringEndsWith($def, $d);
    }

    /**
     * @param  non-empty-string  $filename
     * @param  non-empty-string  $name
     * @param  non-empty-string  $def
     */
    private function assertPropFetch(string $filename, string $name, int $line, string $def): void
    {
        $classes = [PropertyFetch::class, NullsafePropertyFetch::class, StaticPropertyFetch::class];
        $x = $this->findNode(
            $filename,
            $line,
            fn(Node $n): bool => in_array($n::class, $classes, true)
                && isset($n->name)
                && $n->name instanceof Identifier
                && $n->name->toString() === $name,
        );

        self::assertContains($x::class, $classes);
        self::assertTrue(isset($x->name));
        self::assertInstanceOf(Identifier::class, $x->name);

        if ($x instanceof StaticPropertyFetch) {
            $d = $this->types->propDef($x->class, $name);
        } elseif ($x instanceof PropertyFetch || $x instanceof NullsafePropertyFetch) {
            $d = $this->types->propDef($x->var, $name);
        } else {
            $class = $x::class;
            self::fail("Unexpected class: {$class}.");
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
