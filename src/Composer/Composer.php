<?php

declare(strict_types=1);

namespace ScipPhp\Composer;

use Composer\Autoload\ClassLoader;
use Composer\ClassMapGenerator\ClassMapGenerator;
use Composer\ClassMapGenerator\PhpFileParser;
use JetBrains\PHPStormStub\PhpStormStubsMap;
use ReflectionClass;
use ReflectionFunction;
use RuntimeException;
use ScipPhp\File\Reader;

use function array_keys;
use function array_merge;
use function array_slice;
use function array_unique;
use function array_values;
use function class_exists;
use function count;
use function enum_exists;
use function explode;
use function function_exists;
use function get_defined_constants;
use function get_included_files;
use function getcwd;
use function implode;
use function interface_exists;
use function is_array;
use function is_file;
use function is_string;
use function json_decode;
use function preg_match;
use function preg_quote;
use function preg_replace;
use function realpath;
use function rtrim;
use function str_contains;
use function str_replace;
use function str_starts_with;
use function trait_exists;
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

    /** @var non-empty-string */
    private readonly string $scipPhpVendorDir;

    /** @var list<non-empty-string> */
    private readonly array $projectFiles;

    private readonly ClassLoader $loader;

    /** @var non-empty-array<non-empty-string, array{name: non-empty-string, version: non-empty-string}> */
    private array $pkgsByPaths;

    /** @var array<non-empty-string, scalar> */
    private readonly array $userConsts;

    /**
     * @param  non-empty-string  $elem
     * @param  non-empty-string  $elems
     * @return non-empty-string
     */
    private static function join(string $elem, string ...$elems): string
    {
        return implode(DIRECTORY_SEPARATOR, [$elem, ...$elems]);
    }

    /** @return non-empty-string */
    private static function trimPathSeparators(string $path): string
    {
        $trimmed = rtrim($path, '/\\');
        if ($trimmed === '') {
            return $path;
        }
        return $trimmed;
    }

    /** @return ?non-empty-string */
    private static function discoverScipPhpVendorDir(): ?string
    {
        $packageRoot = self::join(__DIR__, '..', '..');
        $candidates = [
            self::join($packageRoot, 'vendor'),
            self::join($packageRoot, '..', '..'),
        ];
        foreach ($candidates as $candidate) {
            $autoload = self::join($candidate, 'autoload.php');
            if (!is_file($autoload)) {
                continue;
            }
            $realPath = realpath($candidate);
            if ($realPath !== false) {
                return $realPath;
            }
        }
        return null;
    }

    /** @param  non-empty-string  $projectRoot */
    public function __construct(private readonly string $projectRoot)
    {
        $json = $this->parseJson('composer.json');
        $autoload = is_array($json['autoload'] ?? null) ? $json['autoload'] : [];
        $autoloadDev = is_array($json['autoload-dev'] ?? null) ? $json['autoload-dev'] : [];

        $scipPhpVendorDir = self::discoverScipPhpVendorDir();
        if ($scipPhpVendorDir === null) {
            // If the vendor directory relative to this file is not found, scip-php probably runs as a
            // dev dependency of the project that it analyses and shares the vendor directory with it.
            $cwd = getcwd();
            if ($cwd === false) {
                throw new RuntimeException("Cannot get the current working directory.");
            }
            $scipPhpVendorDir = self::join($cwd, 'vendor');
            if (realpath($scipPhpVendorDir) === false) {
                throw new RuntimeException("Invalid scip-php vendor directory: {$scipPhpVendorDir}.");
            }
        }
        $scipPhpVendorDirRealPath = realpath($scipPhpVendorDir);
        if ($scipPhpVendorDirRealPath === false) {
            throw new RuntimeException("Cannot get absoute path to {$scipPhpVendorDir}.");
        }
        $this->scipPhpVendorDir = $scipPhpVendorDirRealPath;

        $bin = [];
        if (is_array($json['bin'] ?? null)) {
            $bin = $this->collectPaths($json['bin']);
        }
        $this->projectFiles = array_merge(
            $bin,
            $this->loadProjectFiles($autoload),
            $this->loadProjectFiles($autoloadDev),
        );

        $vendorDir = 'vendor';
        if (
            is_array($json['config'] ?? null)
            && is_string($json['config']['vendor-dir'] ?? null)
        ) {
            $dir = trim($json['config']['vendor-dir'], '/\\');
            if ($dir !== '') {
                $vendorDir = $dir;
            }
        }
        $this->vendorDir = self::join($projectRoot, $vendorDir);

        $projectAutoload = Reader::read(self::join($this->vendorDir, 'autoload.php'));
        $scipPhpAutoload = Reader::read(self::join($this->scipPhpVendorDir, 'autoload.php'));
        $autoloadDir = $projectAutoload === $scipPhpAutoload ? $this->scipPhpVendorDir : $this->vendorDir;
        $loader = require self::join($autoloadDir, 'autoload.php');
        if (!$loader instanceof ClassLoader) {
            throw new RuntimeException("Cannot get autoload.php class loader.");
        }
        if ($autoloadDir !== $this->scipPhpVendorDir) {
            $loader->unregister();
        }
        $this->loader = $loader;

        $installed = require self::join($this->vendorDir, 'composer', 'installed.php');

        if (!is_array($installed) || !is_array($installed['root'])) {
            throw new RuntimeException("Cannot get root element from installed.php.");
        }

        $pkgName = $installed['root']['name'];
        if (!is_string($pkgName) || $pkgName === '') {
            throw new RuntimeException("Cannot get package name.");
        }
        $this->pkgName = $pkgName;

        $pkgVersion = $installed['root']['reference'] ?? $installed['root']['version'];
        if (!is_string($pkgVersion) || $pkgVersion === '') {
            throw new RuntimeException("Cannot get package version.");
        }
        $this->pkgVersion = $pkgVersion;

        $additionalClasses = [];
        foreach ($this->projectFiles as $f) {
            $classes = PhpFileParser::findClasses($f);
            foreach ($classes as $c) {
                if ($this->loader->findFile($c) === false) {
                    $additionalClasses[$c] = $f;
                }
            }
        }
        $this->loader->addClassMap($additionalClasses);

        $pkgsByPaths = [];
        if (is_array($installed['versions'])) {
            foreach ($installed['versions'] as $name => $info) {
                if (!is_string($name) || $name === '') {
                    continue;
                }
                // Replaced packages do not have an install path.
                // See https://getcomposer.org/doc/04-schema.md#replace
                if (!is_array($info) || !is_string($info['install_path'] ?? null)) {
                    continue;
                }
                $path = realpath($info['install_path']);
                if ($path === false) {
                    throw new RuntimeException("Invalid install path of package {$name}: {$info['install_path']}.");
                }
                if ($name !== $this->pkgName && is_string($info['reference']) && $info['reference'] !== '') {
                    $pkgsByPaths[$path] = ['name' => $name, 'version' => $info['reference']];
                }
            }
        }

        $composerPath = self::join($this->vendorDir, 'composer');
        $pkgsByPaths[$composerPath] = ['name' => 'composer', 'version' => 'dev'];
        $this->pkgsByPaths = $pkgsByPaths;


        $lock = $this->parseJson('composer.lock');
        if (is_array($lock['packages'] ?? null)) {
            foreach ($lock['packages'] as $pkg) {
                if (
                    !is_array($pkg)
                    || !is_array($pkg['autoload'] ?? null)
                    || !is_array($pkg['autoload']['files'] ?? null)
                    || !is_string($pkg['name'] ?? null)
                    || $pkg['name'] === ''
                ) {
                    continue;
                }
                foreach ($pkg['autoload']['files'] as $f) {
                    if (!is_string($f) || $f === '') {
                        continue;
                    }
                    $f = self::join($this->vendorDir, $pkg['name'], $f);
                    $classes = PhpFileParser::findClasses($f);
                    foreach ($classes as $c) {
                        if ($this->loader->findFile($c) === false) {
                            $additionalClasses[$c] = $f;
                        }
                    }
                }
            }
        }
        $this->loader->addClassMap($additionalClasses);

        $this->userConsts = get_defined_constants(categorize: true)['user'] ?? []; // @phpstan-ignore-line
    }

    /**
     * @param  non-empty-string  $filename
     * @return array<array-key, mixed>
     */
    private function parseJson(string $filename): array
    {
        $content = Reader::read(self::join($this->projectRoot, $filename));
        $json = json_decode($content, associative: true, flags: JSON_THROW_ON_ERROR);
        if (!is_array($json)) {
            throw new RuntimeException("Cannot parse {$filename}.");
        }
        return $json;
    }

    /**
     * @param  array<array-key, mixed>  $autoload
     * @return array<int, non-empty-string>
     */
    private function loadProjectFiles(array $autoload): array
    {
        $generator = new ClassMapGenerator();
        $exclusionRegex = null;
        if (is_array($autoload['exclude-from-classmap'] ?? null) && count($autoload['exclude-from-classmap']) > 0) {
            $exclusions = [];
            foreach ($autoload['exclude-from-classmap'] as $e) {
                if (is_string($e) && $e !== '') {
                    $exclusions[] = $e;
                }
            }
            $exclusionRegex = '{(' . implode('|', $exclusions) . ')}';
        }
        if (is_array($autoload['classmap'] ?? null)) {
            foreach ($autoload['classmap'] as $path) {
                if (!is_string($path) || $path === '') {
                    continue;
                }
                $p = self::join($this->projectRoot, $path);
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
                    $p = self::join($this->projectRoot, $path);
                    $p = self::trimPathSeparators($p);
                    $generator->scanPaths($p, $exclusionRegex, $t, $ns);
                }
            }
        }

        $map = $generator->getClassMap();
        $map->sort();
        $classFiles = array_unique(array_values($map->getMap()));

        if (!is_array($autoload['files'] ?? null)) {
            return $classFiles;
        }
        $files = $this->collectPaths($autoload['files']);
        return array_merge($files, $classFiles);
    }

    /**
     * @param  array<array-key, mixed>  $paths
     * @return list<non-empty-string>
     */
    private function collectPaths(array $paths): array
    {
        $files = [];
        foreach ($paths as $p) {
            if (!is_string($p) || $p === '') {
                continue;
            }
            $p = self::join($this->projectRoot, $p);
            $p = realpath($p);
            if ($p !== false) {
                $files[] = $p;
            }
        }
        return $files;
    }

    /** @return list<non-empty-string> */
    public function projectFiles(): array
    {
        return $this->projectFiles;
    }

    /** @param  non-empty-string  $ident */
    public function isDependency(string $ident): bool
    {
        return !$this->isFromProject($ident);
    }

    /** @param  non-empty-string  $c */
    public function isConst(string $c): bool
    {
        return isset(PhpStormStubsMap::CONSTANTS[$c]) || isset($this->userConsts[$c]);
    }

    /** @param  non-empty-string  $c */
    public function isClassLike(string $c): bool
    {
        return isset(PhpStormStubsMap::CLASSES[$c])
            || str_contains($c, 'anon-class-')
            || (str_starts_with($c, 'Composer\\Autoload\\') && class_exists($c))
            || (
                // The goal is to avoid calling {class,interface,trait,enum}_exists if it is not absolutely necessary.
                // This is because if the file contains a fatal error, it will generate a fatal error. Since findFile
                // also returns the path to the file of a namespaced function, check that the identifier is not a
                // function. However, since it is possible that a class-like and a function have the same name, we
                // must call {class,interface,trait,enum}_exists as a last resort.
                $this->loader->findFile($c) !== false && (
                    !function_exists($c)
                    || class_exists($c) || interface_exists($c) || trait_exists($c) || enum_exists($c)
                )
            );
    }

    /** @param  non-empty-string  $f */
    public function isFunc(string $f): bool
    {
        return function_exists($f) || isset(PhpStormStubsMap::FUNCTIONS[$f]) || str_contains($f, 'anon-func-');
    }

    /**
     * @param  non-empty-string  $ident
     * @return ?non-empty-string
     */
    public function findFile(string $ident): ?string
    {
        $stub = $this->stub($ident);
        if ($stub !== null) {
            $f = self::join($this->scipPhpVendorDir, 'jetbrains', 'phpstorm-stubs', $stub);
            $f = realpath($f);
            if ($f === false) {
                throw new RuntimeException("Invalid path to stub file: {$stub}.");
            }
            return $f;
        }

        $f = $this->loader->findFile($ident);
        if ($f !== false) {
            $f = realpath($f);
            if ($f !== false) {
                return $f;
            }
        }

        if (function_exists($ident)) {
            $func = new ReflectionFunction($ident);
            $f = $func->getFileName();
            if ($f !== false && $f !== '') {
                if (!str_contains($f, $this->scipPhpVendorDir)) {
                    return $f;
                }
                // In case of a conflict between a function defined in a dependency of scip-php
                // and a function defined in the analyzed project or its dependencies, the
                // former is used here. Therefore, we patch the path, so that the latter is
                // analyzed instead.
                $vendorFile = str_replace($this->scipPhpVendorDir, $this->vendorDir, $f);
                if (is_file($vendorFile)) {
                    return $vendorFile;
                }
                // If the file is not found in the vendor directory, we probably analyze
                // a project which is also a dependency of scip-php.
                $f = str_replace($this->scipPhpVendorDir . DIRECTORY_SEPARATOR, '', $f);
                $f = preg_replace('/^\w+\/\w+\//', '', $f, limit: 1);
                if ($f === null || $f === '') {
                    throw new RuntimeException("Invalid path to function file: {$func->getFileName()}.");
                }
                return self::join($this->projectRoot, $f);
            }
        }

        if (str_starts_with($ident, 'Composer\\Autoload\\') && class_exists($ident)) {
            $class = new ReflectionClass($ident);
            $f = $class->getFileName();
            if ($f !== false && $f !== '') {
                // In case the analyzed project uses composer classes, patch
                // the path, so that the composer file of the project is analyzed.
                // There is no support for the global composer init classes.
                return str_replace($this->scipPhpVendorDir, $this->vendorDir, $f);
            }
        }
        return $this->findConstFile($ident);
    }

    /**
     * @param  non-empty-string  $ident
     * @return ?array{name: non-empty-string, version: non-empty-string}
     */
    public function pkg(string $ident): ?array
    {
        if ($this->isStub($ident)) {
            return ['name' => 'php', 'version' => PHP_VERSION];
        }
        if ($this->isFromProject($ident)) {
            return ['name' => $this->pkgName, 'version' => $this->pkgVersion];
        }
        $f = $this->findFile($ident);
        if ($f === null) {
            return null;
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
        if (str_contains($ident, 'anon-class-') || str_contains($ident, 'anon-func-')) {
            return true;
        }
        if ($this->isStub($ident)) {
            return false;
        }
        $f = $this->findFile($ident);
        if ($f === null) {
            return false;
        }
        foreach (array_keys($this->pkgsByPaths) as $path) {
            if (str_starts_with($f, $path)) {
                return false;
            }
        }
        return !str_starts_with($f, $this->vendorDir);
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

    /**
     * @param  non-empty-string  $c
     * @return ?non-empty-string
     */
    private function findConstFile(string $c): ?string
    {
        if (!isset($this->userConsts[$c])) {
            return null;
        }

        $parts = explode('\\', $c);
        $last = count($parts) - 1;
        $hasNs = $last > 0;
        $ns = implode('\\', array_slice($parts, 0, $last));
        $const = $parts[$last];
        $ns = preg_quote($ns);
        $qualifiedConst = str_replace('\\', '\\\\', $c);
        $qualifiedConst = preg_quote($qualifiedConst);

        // TODO(drj): replace with an AST visitor.
        $defineConstPattern = "/^\s*define\s*\(\s*['\"]{$qualifiedConst}['\"]\s*,/m";
        $assignConstPattern = "/^\s*const\s+{$const}\s*=/m";
        $nsPattern = "/^\s*namespace\s+{$ns};/m";
        $anyNsPattern = '/^\s*namespace\s+.+;/m';

        $files = get_included_files();
        foreach ($files as $f) {
            if ($f === '') {
                continue;
            }
            $f = realpath($f);
            if ($f === false) {
                continue;
            }

            $content = Reader::read($f);
            if (preg_match($defineConstPattern, $content) === 1) {
                return $f;
            }
            if (preg_match($assignConstPattern, $content) !== 1) {
                continue;
            }
            if ($hasNs && preg_match($nsPattern, $content) === 1) {
                return $f;
            }
            if (!$hasNs && preg_match($anyNsPattern, $content) === 0) {
                return $f;
            }
        }
        return null;
    }
}
