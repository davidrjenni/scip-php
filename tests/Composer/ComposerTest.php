<?php

declare(strict_types=1);

namespace Tests\Composer;

use PHPUnit\Framework\TestCase;
use ScipPhp\Composer\Composer;

use function count;
use function explode;
use function implode;
use function str_starts_with;

use const DIRECTORY_SEPARATOR;
use const PHP_VERSION;

final class ComposerTest extends TestCase
{
    private const BUILTIN = [
        'classes' => ['Exception'],
        'consts' => ['DIRECTORY_SEPARATOR'],
        'funcs' => ['strlen'],
    ];

    private const DEPS = [
        'classes' => ['DeepCopy\\DeepCopy', 'Composer\\Autoload\\ClassLoader'],
        // TODO(drj): 'consts' => [],
        'funcs' => ['DeepCopy\\deep_copy'],
    ];

    private const PROJECT = [
        'classes' => [
            'anon-class-123',
            'TestData1\\ClassA',
            'TestData2\\ClassC',
            'TestDataTests\\ClassATestCase',
            'TestDataTests\\ClassBTestCase',
        ],
        'consts' => [
            'CONST_1',
            'CONST_2',
            'CONST_3',
        ],
        'funcs' => ['fun1'],
    ];

    private const UNKNOWN = [
        'classes' => ['Foo\\Foo'],
        'consts' => ['Foo\\FOO'],
        'funcs' => ['Foo\\foo'],
    ];

    private Composer $composer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->composer = new Composer(__DIR__ . DIRECTORY_SEPARATOR . 'testdata');
    }

    public function testProjectFiles(): void
    {
        $files = $this->composer->projectFiles();

        self::assertCount(4, $files);

        $root = self::join('tests', 'Composer', 'testdata');
        self::assertStringEndsWith(self::join($root, 'bin', 'main'), $files[0]);
        self::assertStringEndsWith(self::join($root, 'src', 'file.php'), $files[1]);
        self::assertStringEndsWith(self::join($root, 'src', 'ClassA.php'), $files[2]);
        self::assertStringEndsWith(self::join($root, 'tests', 'ClassATestCase.php'), $files[3]);
    }

    public function testIsBuiltinClass(): void
    {
        foreach (self::BUILTIN['classes'] as $class) {
            self::assertTrue($this->composer->isBuiltinClass($class), $class);
        }
        foreach (self::BUILTIN['consts'] as $const) {
            self::assertFalse($this->composer->isBuiltinClass($const), $const);
        }
        foreach (self::BUILTIN['funcs'] as $func) {
            self::assertFalse($this->composer->isBuiltinClass($func), $func);
        }

        foreach (self::DEPS as $idents) {
            foreach ($idents as $ident) {
                self::assertFalse($this->composer->isBuiltinClass($ident), $ident);
            }
        }
        foreach (self::PROJECT as $idents) {
            foreach ($idents as $ident) {
                self::assertFalse($this->composer->isBuiltinClass($ident), $ident);
            }
        }
        foreach (self::UNKNOWN as $idents) {
            foreach ($idents as $ident) {
                self::assertFalse($this->composer->isBuiltinClass($ident), $ident);
            }
        }
    }

    public function testIsBuiltinConst(): void
    {
        foreach (self::BUILTIN['classes'] as $class) {
            self::assertFalse($this->composer->isBuiltinConst($class), $class);
        }
        foreach (self::BUILTIN['consts'] as $const) {
            self::assertTrue($this->composer->isBuiltinConst($const), $const);
        }
        foreach (self::BUILTIN['funcs'] as $func) {
            self::assertFalse($this->composer->isBuiltinConst($func), $func);
        }

        foreach (self::DEPS as $idents) {
            foreach ($idents as $ident) {
                self::assertFalse($this->composer->isBuiltinConst($ident), $ident);
            }
        }
        foreach (self::PROJECT as $idents) {
            foreach ($idents as $ident) {
                self::assertFalse($this->composer->isBuiltinConst($ident), $ident);
            }
        }
        foreach (self::UNKNOWN as $idents) {
            foreach ($idents as $ident) {
                self::assertFalse($this->composer->isBuiltinConst($ident), $ident);
            }
        }
    }

    public function testIsBuiltinFunc(): void
    {
        foreach (self::BUILTIN['classes'] as $class) {
            self::assertFalse($this->composer->isBuiltinFunc($class), $class);
        }
        foreach (self::BUILTIN['consts'] as $const) {
            self::assertFalse($this->composer->isBuiltinFunc($const), $const);
        }
        foreach (self::BUILTIN['funcs'] as $func) {
            self::assertTrue($this->composer->isBuiltinFunc($func), $func);
        }

        foreach (self::DEPS as $idents) {
            foreach ($idents as $ident) {
                self::assertFalse($this->composer->isBuiltinFunc($ident), $ident);
            }
        }
        foreach (self::PROJECT as $idents) {
            foreach ($idents as $ident) {
                self::assertFalse($this->composer->isBuiltinFunc($ident), $ident);
            }
        }
        foreach (self::UNKNOWN as $idents) {
            foreach ($idents as $ident) {
                self::assertFalse($this->composer->isBuiltinFunc($ident), $ident);
            }
        }
    }

    public function testIsDependency(): void
    {
        foreach (self::BUILTIN as $idents) {
            foreach ($idents as $ident) {
                self::assertTrue($this->composer->isDependency($ident), $ident);
            }
        }
        foreach (self::DEPS as $idents) {
            foreach ($idents as $ident) {
                self::assertTrue($this->composer->isDependency($ident), $ident);
            }
        }
        foreach (self::PROJECT as $idents) {
            foreach ($idents as $ident) {
                self::assertFalse($this->composer->isDependency($ident), $ident);
            }
        }
        foreach (self::UNKNOWN as $idents) {
            foreach ($idents as $ident) {
                self::assertTrue($this->composer->isDependency($ident), $ident);
            }
        }
    }

    public function testFindFile(): void
    {
        foreach (self::BUILTIN as $idents) {
            foreach ($idents as $ident) {
                $f = $this->composer->findFile($ident);
                self::assertNotNull($f, $ident);
                self::assertStringEndsWith('.php', $f);
                self::assertStringContainsString(self::join('jetbrains', 'phpstorm-stubs'), $f);
            }
        }
        foreach (self::DEPS as $type => $idents) {
            foreach ($idents as $ident) {
                $f = $this->composer->findFile($ident);
                self::assertNotNull($f, $ident);
                self::assertStringContainsString(self::join('tests', 'Composer', 'testdata', 'vendor'), $f);
                self::assertStringEndsWith('.php', $f);

                if ($type === 'classes') {
                    $parts = explode('\\', $ident);
                    $class = $parts[count($parts) - 1];
                    self::assertStringEndsWith("{$class}.php", $f);
                }
            }
        }
        foreach (self::PROJECT as $type => $idents) {
            foreach ($idents as $ident) {
                $f = $this->composer->findFile($ident);
                if (str_starts_with($ident, 'anon-class-')) {
                    self::assertNull($f, $ident);
                    continue;
                }

                self::assertNotNull($f, $ident);
                self::assertStringContainsString(self::join('tests', 'Composer', 'testdata'), $f);
                self::assertStringNotContainsString('vendor', $f);
                self::assertStringEndsWith('.php', $f);
            }
        }
        foreach (self::UNKNOWN as $idents) {
            foreach ($idents as $ident) {
                self::assertNull($this->composer->findFile($ident), $ident);
            }
        }
    }

    public function testPkg(): void
    {
        foreach (self::BUILTIN as $idents) {
            foreach ($idents as $ident) {
                self::assertEquals(
                    ['name' => 'php', 'version' => PHP_VERSION],
                    $this->composer->pkg($ident),
                    $ident,
                );
            }
        }

        self::assertEquals(
            ['name' => 'myclabs/deep-copy', 'version' => '1.11.1'],
            $this->composer->pkg(self::DEPS['funcs'][0]),
        );

        self::assertEquals(
            ['name' => 'myclabs/deep-copy', 'version' => '1.11.1'],
            $this->composer->pkg(self::DEPS['classes'][0]),
        );

        self::assertEquals(
            ['name' => 'composer', 'version' => 'dev'],
            $this->composer->pkg(self::DEPS['classes'][1]),
        );

        foreach (self::PROJECT as $idents) {
            foreach ($idents as $ident) {
                $pkg = $this->composer->pkg($ident);
                self::assertNotNull($pkg, $ident);
                self::assertEquals('davidrjenni/scip-php-composer-test', $pkg['name'], $ident);
            }
        }
        foreach (self::UNKNOWN as $idents) {
            foreach ($idents as $ident) {
                self::assertNull($this->composer->pkg($ident), $ident);
            }
        }
    }

    /**
     * @param  non-empty-string $elem
     * @param  non-empty-string $elems
     * @return non-empty-string
     */
    private static function join(string $elem, string ...$elems): string
    {
        return implode(DIRECTORY_SEPARATOR, [$elem, ...$elems]);
    }
}
