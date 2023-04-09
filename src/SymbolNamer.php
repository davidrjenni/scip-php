<?php

declare(strict_types=1);

namespace ScipPhp;

use LogicException;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Trait_;
use RuntimeException;
use ScipPhp\Composer\Composer;

use function class_exists;
use function count;
use function function_exists;
use function str_replace;

final class SymbolNamer
{
    private const SCHEME = 'scip-php';

    private const MANAGER = 'composer';

    public function __construct(private readonly Composer $composer)
    {
    }

    /** @return ?non-empty-string */
    public function name(Name $n): ?string
    {
        if ($n->toString() === 'self' || $n->toString() ===  'static') {
            $ns = $this->namespaceName($n);
            $class = $this->classLikeName($n);
            return $this->desc("{$ns}{$class}", '#');
        }

        if ($n->toString() === 'parent') {
            $classLike = $this->classLike($n);
            if ($classLike instanceof Class_) {
                if ($classLike->extends === null) {
                    throw new LogicException('Reference to parent in class without parent class.');
                }
                return $this->name($classLike->extends);
            }
            if ($classLike instanceof Trait_) {
                return null;
            }
            throw new LogicException('Reference to parent in unexpected node type: ' . $classLike::class . '.');
        }

        $name = $n->getLast();
        if ($name === '') {
            throw new RuntimeException("Last part of name is empty: {$n}.");
        }

        // Fully-qualified, namespaced name.
        if (count($n->parts) > 1) {
            $ns = str_replace("\\", '/', $n->slice(0, -1)?->toString() ?? '');
            if ($ns !== '') {
                $ns = "{$ns}/";
            }
            return $this->desc("{$ns}{$name}", '#');
        }

        if (class_exists($name)) {
            return $this->desc($name, '#');
        }
        if (function_exists($name)) {
            return $this->desc($name, '().');
        }
        if ($this->composer->isBuiltinConst($name)) {
            return $this->desc($name, '.');
        }
        return null;
    }

    private function namespaceName(Node $n): string
    {
        while (true) {
            $n = $n->getAttribute('parent');
            if ($n === null) {
                return '';
            }
            if ($n instanceof Namespace_) {
                $ns = str_replace("\\", '/', $n->name?->toString() ?? '');
                if ($ns !== '') {
                    return "{$ns}/";
                }
                return '';
            }
        }
    }

    /** @return non-empty-string */
    private function classLikeName(Node $n): string
    {
        $c = $this->classLike($n);
        $name = $c->name?->toString();
        if ($name === null || $name === '') {
            return "anon-class-{$c->getStartTokenPos()}";
        }
        return $name;
    }

    private function classLike(Node $n): ClassLike
    {
        while (true) {
            $n = $n->getAttribute('parent');
            if ($n === null) {
                throw new LogicException('Cannot find ClassLike.');
            }
            if ($n instanceof ClassLike) {
                return $n;
            }
        }
    }

    /**
     * @param  non-empty-string  $name
     * @param  non-empty-string  $suffix
     * @return ?non-empty-string
     */
    private function desc(string $name, string $suffix): ?string
    {
        $c = str_replace('/', '\\', $name);
        $pkg = $this->composer->pkg($c);
        if ($pkg === null) {
            return null;
        }
        ['name' => $pkgName, 'version' => $version] = $pkg;
        return self::SCHEME . ' ' . self::MANAGER . " {$pkgName} {$version} {$name}{$suffix}";
    }
}
