<?php

declare(strict_types=1);

namespace ScipPhp\Composer;

use Composer\Autoload\ClassLoader;
use Composer\ClassMapGenerator\ClassMapGenerator;
use JetBrains\PHPStormStub\PhpStormStubsMap;
use ReflectionClass;
use ReflectionFunction;
use RuntimeException;
use ScipPhp\File\Reader;

use function array_merge;
use function array_values;
use function class_exists;
use function count;
use function function_exists;
use function implode;
use function is_array;
use function is_string;
use function json_decode;
use function realpath;
use function rtrim;
use function str_contains;
use function str_starts_with;
use function trim;

use const DIRECTORY_SEPARATOR;
use const JSON_THROW_ON_ERROR;
use const PHP_VERSION;

final class Composer
{
    /** @var non-empty-string */
    private readonly string $pkgName;

    /** @var non-empty-string */
    private readonly string $pkgVersion;

    /** @var non-empty-string */
    private readonly string $vendorDir;

    /** @var array<int, non-empty-string> */
    private readonly array $projectFiles;

    private readonly ClassLoader $loader;

    /** @var array<non-empty-string, array{name: non-empty-string, version: non-empty-string}> */
    private array $pkgsByPaths;

    /**
     * @param  non-empty-string  $projectRoot
     * @param  non-empty-string  $filename
     * @return array<string, mixed>
     */
    private static function parseJson(string $projectRoot, string $filename): array
    {
        $content = Reader::read(self::join($projectRoot, $filename));
        $json = json_decode($content, associative: true, flags: JSON_THROW_ON_ERROR);
        if (!is_array($json)) {
            throw new RuntimeException("Cannot parse {$filename}.");
        }
        return $json;
    }

    /**
     * @param  non-empty-string  $projectRoot
     * @param  array<string, mixed>  $autoload
     * @return array<int, non-empty-string>
     */
    private static function loadProjectFiles(string $projectRoot, array $autoload): array
    {
        $generator = new ClassMapGenerator();
        $exclusionRegex = null;
        if (is_array($autoload['exclude-from-classmap'] ?? null) && count($autoload['exclude-from-classmap']) > 0) {
            $exclusionRegex = '{(' . implode('|', $autoload['exclude-from-classmap']) . ')}';
        }
        if (is_array($autoload['classmap'] ?? null)) {
            foreach ($autoload['classmap'] as $path) {
                $p = self::join($projectRoot, $path);
                $generator->scanPaths($p, $exclusionRegex);
            }
        }
        foreach (['psr-4', 'psr-0'] as $t) {
            if (!is_array($autoload[$t] ?? null)) {
                continue;
            }
            foreach ($autoload[$t] as $ns => $paths) {
                if (!is_string($ns) || $ns === '' || (!is_array($paths) && !is_string($paths))) {
                    continue;
                }
                $paths = is_string($paths) ? [$paths] : $paths;
                foreach ($paths as $path) {
                    if (!is_string($path) || $path === '') {
                        continue;
                    }
                    $p = self::join($projectRoot, $path);
                    $p = rtrim($p, DIRECTORY_SEPARATOR);
                    $generator->scanPaths($p, $exclusionRegex, $t, $ns);
                }
            }
        }

        $map = $generator->getClassMap();
        $map->sort();

        // TODO(drj): add $autoload['files']
        return array_values($map->getMap());
    }

    /**
     * @param  non-empty-string  $elem
     * @param  non-empty-string  $elems
     * @return non-empty-string
     */
    private static function join(string $elem, string ...$elems): string
    {
        return implode(DIRECTORY_SEPARATOR, [$elem, ...$elems]);
    }

    /** @param  non-empty-string  $projectRoot */
    public function __construct(string $projectRoot)
    {
        $json = self::parseJson($projectRoot, 'composer.json');
        $autoload = is_array($json['autoload'] ?? null) ? $json['autoload'] : [];
        $autoloadDev = is_array($json['autoload-dev'] ?? null) ? $json['autoload-dev'] : [];

        // TODO(drj): add $json['bin']
        $this->projectFiles = array_merge(
            self::loadProjectFiles($projectRoot, $autoload),
            self::loadProjectFiles($projectRoot, $autoloadDev),
        );

        $vendorDir = 'vendor';
        if (
            is_array($json['config'] ?? null)
            && is_string($json['config']['vendor-dir'] ?? null)
            && trim($json['config']['vendor-dir'], '/') !== ''
        ) {
            $vendorDir = trim($json['config']['vendor-dir'], '/');
        }
        $this->vendorDir = self::join($projectRoot, $vendorDir);
        $this->loader = require self::join($this->vendorDir, 'autoload.php');

        $installed = require self::join($this->vendorDir, 'composer', 'installed.php');
        $this->pkgName = $installed['root']['name'];
        $this->pkgVersion = $installed['root']['reference'];

        $pkgsByPaths = [];
        foreach ($installed['versions'] as $name => $info) {
            $path = realpath($info['install_path']);
            if ($path === false) {
                throw new RuntimeException("Invalid install path of package {$name}: {$info['install_path']}.");
            }
            if ($name !== $this->pkgName) {
                $pkgsByPaths[$path] = ['name' => $name, 'version' => $info['pretty_version']];
            }
        }

        $composerPath = self::join($this->vendorDir, 'composer');
        $pkgsByPaths[$composerPath] = ['name' => 'composer', 'version' => 'dev'];
        $this->pkgsByPaths = $pkgsByPaths;
    }

    /** @return array<int, non-empty-string> */
    public function projectFiles(): array
    {
        return $this->projectFiles;
    }

    /** @param  non-empty-string  $c */
    public function isBuiltinClass(string $c): bool
    {
        return isset(PhpStormStubsMap::CLASSES[$c]);
    }

    /** @param  non-empty-string  $c */
    public function isBuiltinConst(string $c): bool
    {
        return isset(PhpStormStubsMap::CONSTANTS[$c]);
    }

    /** @param  non-empty-string  $f */
    public function isBuiltinFunc(string $f): bool
    {
        return isset(PhpStormStubsMap::FUNCTIONS[$f]);
    }

    /** @param  non-empty-string  $ident */
    public function isDependency(string $ident): bool
    {
        return !$this->isFromProject($ident);
    }

    /**
     * @param  non-empty-string  $ident
     * @return ?non-empty-string
     */
    public function findFile(string $ident): ?string
    {
        $stub = $this->stub($ident);
        if ($stub !== null) {
            $f = self::join(__DIR__, '..', '..', 'vendor', 'jetbrains', 'phpstorm-stubs', $stub);
            $f = realpath($f);
            if ($f === false) {
                throw new RuntimeException("Invalid path to stub file: {$stub}.");
            }
            return $f;
        }
        $f = $this->loader->findFile($ident);
        if ($f !== false && realpath($f) !== false) {
            return realpath($f);
        }
        if (function_exists($ident)) {
            $func = new ReflectionFunction($ident);
            $f = $func->getFileName();
            if ($f !== false && $f !== '') {
                return $f;
            }
        }
        if (class_exists($ident)) {
            $class = new ReflectionClass($ident);
            $f = $class->getFileName();
            if ($f !== false && $f !== '') {
                return $f;
            }
        }
        // TODO(drj): resolve constant location
        return null;
    }

    /**
     * @param  non-empty-string  $ident
     * @return array{name: non-empty-string, version: non-empty-string}
     */
    public function pkg(string $ident): array
    {
        if ($this->isStub($ident)) {
            return ['name' => 'php', 'version' => PHP_VERSION];
        }
        if ($this->isFromProject($ident)) {
            return ['name' => $this->pkgName, 'version' => $this->pkgVersion];
        }
        $f = $this->findFile($ident);
        if ($f === null) {
            throw new RuntimeException("Cannot find file for identifier: {$ident}.");
        }
        foreach ($this->pkgsByPaths as $path => $info) {
            if (str_starts_with($f, $path)) {
                return $info;
            }
        }
        throw new RuntimeException("Cannot find package for identifier {$ident} in file {$f}.");
    }

    /** @param  non-empty-string  $ident */
    private function isFromProject(string $ident): bool
    {
        if (str_contains($ident, 'anon-class-')) {
            return true;
        }
        if (str_starts_with($ident, 'Composer\\Autoload\\') || $this->isStub($ident)) {
            return false;
        }
        $f = $this->findFile($ident);
        return $f !== null && !str_starts_with($f, $this->vendorDir);
    }

    /** @param  non-empty-string  $ident */
    private function isStub(string $ident): bool
    {
        return $this->stub($ident) !== null || $ident === 'IntBackedEnum' || $ident === 'StringBackedEnum';
    }

    /**
     * @param  non-empty-string  $ident
     * @return ?non-empty-string
     */
    private function stub(string $ident): ?string
    {
        return PhpStormStubsMap::CLASSES[$ident]
            ?? PhpStormStubsMap::FUNCTIONS[$ident]
            ?? PhpStormStubsMap::CONSTANTS[$ident]
            ?? null;
    }
}
