<?php

declare(strict_types=1);

namespace Tests\Composer;

use PHPUnit\Framework\TestCase;
use ScipPhp\Composer\Composer;

use function count;
use function explode;
use function implode;

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
        'classes' => ['anon-class-123', 'TestData\\ClassA', 'TestDataTests\\ClassATestCase'],
        // TODO(drj): 'consts' => [],
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

        self::assertCount(2, $files);

        self::assertStringEndsWith(
            self::join('tests', 'Composer', 'testdata', 'src', 'ClassA.php'),
            $files[0],
        );

        self::assertStringEndsWith(
            self::join('tests', 'Composer', 'testdata', 'tests', 'ClassATestCase.php'),
            $files[1],
        );
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
                self::assertNotNull($f);
                self::assertStringEndsWith('.php', $f);
                self::assertStringContainsString(self::join('jetbrains', 'phpstorm-stubs'), $f);
            }
        }
        foreach (self::DEPS as $type => $idents) {
            foreach ($idents as $ident) {
                $f = $this->composer->findFile($ident);
                self::assertNotNull($f);
                if ($type === 'classes') {
                    $parts = explode('\\', $ident);
                    $class = $parts[count($parts) - 1];
                    self::assertStringEndsWith("{$class}.php", $f);
                } else {
                    self::assertStringEndsWith('.php', $f);
                }
                /* self::assertStringContainsString(self::join('tests', 'Composer', 'testdata', 'vendor'), $f); */
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
                );
            }
        }
        foreach (self::DEPS['funcs'] as $ident) {
            $this->composer->pkg($ident);
        }
        foreach (self::PROJECT as $idents) {
            foreach ($idents as $ident) {
                self::assertEquals(
                    'davidrjenni/scip-php-composer-test',
                    $this->composer->pkg($ident)['name'],
                );
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
