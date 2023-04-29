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
            'TestDataTests\\EnumC',
            'TestDataTests\\TraitD',
            'TestDataTests\\InterfaceE',
        ],
        'consts' => [
            'CONST_1',
            'CONST_2',
            'CONST_3',
        ],
        'funcs' => ['anon-func-123', 'fun1'],
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

    public function testIsBuiltinConst(): void
    {
        foreach (self::BUILTIN as $type => $idents) {
            foreach ($idents as $ident) {
                self::assertSame($type === 'consts', $this->composer->isBuiltinConst($ident), $ident);
            }
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

    public function testIsFunc(): void
    {
        foreach (self::BUILTIN as $type => $idents) {
            foreach ($idents as $ident) {
                self::assertSame($type === 'funcs', $this->composer->isFunc($ident), $ident);
            }
        }
        foreach (self::DEPS as $type => $idents) {
            foreach ($idents as $ident) {
                self::assertSame($type === 'funcs', $this->composer->isFunc($ident), $ident);
            }
        }
        foreach (self::PROJECT as $type => $idents) {
            foreach ($idents as $ident) {
                self::assertSame($type === 'funcs', $this->composer->isFunc($ident), $ident);
            }
        }
        foreach (self::UNKNOWN as $idents) {
            foreach ($idents as $ident) {
                self::assertFalse($this->composer->isFunc($ident), $ident);
            }
        }
    }

    public function testIsClassLike(): void
    {
        foreach (self::BUILTIN as $type => $idents) {
            foreach ($idents as $ident) {
                self::assertSame($type === 'classes', $this->composer->isClassLike($ident), $ident);
            }
        }
        foreach (self::DEPS as $type => $idents) {
            foreach ($idents as $ident) {
                self::assertSame($type === 'classes', $this->composer->isClassLike($ident), $ident);
            }
        }
        foreach (self::PROJECT as $type => $idents) {
            foreach ($idents as $ident) {
                self::assertSame($type === 'classes', $this->composer->isClassLike($ident), $ident);
            }
        }
        foreach (self::UNKNOWN as $idents) {
            foreach ($idents as $ident) {
                self::assertFalse($this->composer->isClassLike($ident), $ident);
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
        foreach (self::PROJECT as $idents) {
            foreach ($idents as $ident) {
                $f = $this->composer->findFile($ident);
                if (str_starts_with($ident, 'anon-')) {
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
                self::assertSame(
                    ['name' => 'php', 'version' => PHP_VERSION],
                    $this->composer->pkg($ident),
                    $ident,
                );
            }
        }

        foreach (self::DEPS['funcs'] as $ident) {
            $pkg = $this->composer->pkg($ident);
            self::assertNotNull($pkg, $ident);
            ['name' => $name, 'version' => $version] = $pkg;
            self::assertSame('myclabs/deep-copy', $name);
            self::assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $version);
        }

        $pkg = $this->composer->pkg(self::DEPS['classes'][0]);
        self::assertNotNull($pkg);
        ['name' => $name, 'version' => $version] = $pkg;
        self::assertSame('myclabs/deep-copy', $name);
        self::assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $version);

        self::assertSame(
            ['name' => 'composer', 'version' => 'dev'],
            $this->composer->pkg(self::DEPS['classes'][1]),
        );

        foreach (self::PROJECT as $idents) {
            foreach ($idents as $ident) {
                $pkg = $this->composer->pkg($ident);
                self::assertNotNull($pkg, $ident);
                self::assertSame('davidrjenni/scip-php-composer-test', $pkg['name'], $ident);
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
