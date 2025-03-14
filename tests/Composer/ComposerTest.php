<?php

declare(strict_types=1);

namespace Tests\Composer;

use Override;
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
    private const array BUILTIN = [
        'classes' => ['Exception'],
        'consts' => ['DIRECTORY_SEPARATOR'],
        'funcs' => ['strlen'],
    ];

    private const array DEPS = [
        'classes' => ['DeepCopy\\DeepCopy', 'Composer\\Autoload\\ClassLoader'],
        // TODO(drj): 'consts' => [],
        'funcs' => ['DeepCopy\\deep_copy'],
    ];

    private const array PROJECT = [
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
            'TestData3\\Foo\\CONST_4',
            'TestData3\\Foo\\CONST_5',
            'CONST_6',
        ],
        'funcs' => ['anon-func-123', 'fun1', 'TestData3\\Foo\\fun1'],
    ];

    private const array UNKNOWN = [
        'classes' => ['Foo\\Foo', 'Foo'],
        'consts' => ['Foo\\FOO', 'FOO'],
        'funcs' => ['Foo\\foo', 'foo'],
    ];

    private Composer $composer;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->composer = new Composer(__DIR__ . DIRECTORY_SEPARATOR . 'testdata');
    }

    public function testProjectFiles(): void
    {
        $files = $this->composer->projectFiles();

        self::assertCount(5, $files);

        $root = self::join('tests', 'Composer', 'testdata');
        self::assertStringEndsWith(self::join($root, 'bin', 'main'), $files[0]);
        self::assertStringEndsWith(self::join($root, 'src', 'file1.php'), $files[1]);
        self::assertStringEndsWith(self::join($root, 'src', 'file2.php'), $files[2]);
        self::assertStringEndsWith(self::join($root, 'src', 'ClassA.php'), $files[3]);
        self::assertStringEndsWith(self::join($root, 'tests', 'ClassATestCase.php'), $files[4]);
    }

    public function testIsDependency(): void
    {
        foreach ([...self::BUILTIN, ...self::DEPS, ...self::UNKNOWN] as $idents) {
            foreach ($idents as $ident) {
                self::assertTrue($this->composer->isDependency($ident), $ident);
            }
        }
        foreach (self::PROJECT as $idents) {
            foreach ($idents as $ident) {
                self::assertFalse($this->composer->isDependency($ident), $ident);
            }
        }
    }

    public function testIsConst(): void
    {
        foreach ([...self::BUILTIN, ...self::DEPS, ...self::PROJECT] as $type => $idents) {
            foreach ($idents as $ident) {
                self::assertSame($type === 'consts', $this->composer->isConst($ident), $ident);
            }
        }
        foreach (self::UNKNOWN as $idents) {
            foreach ($idents as $ident) {
                self::assertFalse($this->composer->isConst($ident), $ident);
            }
        }
    }

    public function testIsFunc(): void
    {
        foreach ([...self::BUILTIN, ...self::DEPS, ...self::PROJECT] as $type => $idents) {
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
        foreach ([...self::BUILTIN, ...self::DEPS, ...self::PROJECT] as $type => $idents) {
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

                if ($type !== 'classes') {
                    continue;
                }

                $parts = explode('\\', $ident);
                $class = $parts[count($parts) - 1];
                self::assertStringEndsWith("{$class}.php", $f);
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
