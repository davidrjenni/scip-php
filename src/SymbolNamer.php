<?php

declare(strict_types=1);

namespace ScipPhp;

use LogicException;
use PhpParser\Node;
use PhpParser\Node\Const_;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\EnumCase;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Trait_;
use RuntimeException;
use ScipPhp\Composer\Composer;

use function count;
use function explode;
use function is_string;
use function rtrim;
use function str_replace;
use function strpos;
use function substr;

final readonly class SymbolNamer
{
    private const string SCHEME = 'scip-php';

    private const string MANAGER = 'composer';

    public function __construct(private Composer $composer)
    {
    }

    /**
     * Returns the fully-qualified class, function or constant name of the given symbol.
     *
     * @param  non-empty-string  $symbol
     * @return non-empty-string
     */
    public function extractIdent(string $symbol): string
    {
        $parts = explode(' ', $symbol);
        if (count($parts) !== 5) {
            throw new RuntimeException("Invalid symbol: {$symbol}.");
        }

        $desc = $parts[4];
        $i = strpos($desc, '#');
        if ($i !== false) {
            $desc = substr($desc, 0, $i);
        }

        $desc = str_replace('/', '\\', $desc);
        $desc = rtrim($desc, '.');
        $desc = rtrim($desc, '()');
        if ($desc === '') {
            throw new LogicException("Cannot extract identifier from symbol: {$symbol}.");
        }
        return $desc;
    }

    /**
     * @param  non-empty-string  $symbol
     * @param  non-empty-string  $const
     * @return non-empty-string
     */
    public function nameConst(string $symbol, string $const): string
    {
        return "{$symbol}{$const}.";
    }

    /**
     * @param  non-empty-string  $symbol
     * @param  non-empty-string  $meth
     * @return non-empty-string
     */
    public function nameMeth(string $symbol, string $meth): string
    {
        return "{$symbol}{$meth}().";
    }

    /**
     * @param  non-empty-string  $symbol
     * @param  non-empty-string  $param
     * @return non-empty-string
     */
    public function nameParam(string $symbol, string $param): string
    {
        return "{$symbol}(\${$param})";
    }

    /**
     * @param  non-empty-string  $symbol
     * @param  non-empty-string  $prop
     * @return non-empty-string
     */
    public function nameProp(string $symbol, string $prop): string
    {
        return "{$symbol}\${$prop}.";
    }

    /** @return ?non-empty-string */
    public function nameNearestClassLike(Node $n): ?string
    {
        $ns = $this->namespaceName($n);
        $class = $this->classLikeName($n);
        if ($class === null) {
            return null;
        }
        return $this->desc("{$ns}{$class}", '#');
    }

    /** @return ?non-empty-string */
    public function name(Const_|ClassLike|EnumCase|FunctionLike|Name|Param|PropertyItem $n): ?string
    {
        if ($n instanceof ArrowFunction || $n instanceof Closure) {
            $ns = $this->namespaceName($n);
            $func = "anon-func-{$n->getStartTokenPos()}";
            return $this->desc("{$ns}{$func}", '().');
        }

        if ($n instanceof Const_) {
            $ns = $this->namespaceName($n);
            $class = $this->classLikeName($n);
            if ($class === null) {
                return null;
            }
            return $this->desc("{$ns}{$class}", "#{$n->name}.");
        }

        if ($n instanceof ClassLike) {
            $ns = $this->namespaceName($n);
            $class = $n->name?->toString() ?? null;
            if ($class === null || $class === '') {
                $class = "anon-class-{$n->getStartTokenPos()}";
            }
            return $this->desc("{$ns}{$class}", '#');
        }

        if ($n instanceof ClassMethod) {
            $ns = $this->namespaceName($n);
            $class = $this->classLikeName($n);
            if ($class === null) {
                return null;
            }
            return $this->desc("{$ns}{$class}", "#{$n->name}().");
        }

        if ($n instanceof EnumCase) {
            $ns = $this->namespaceName($n);
            $class = $this->classLikeName($n);
            if ($class === null) {
                return null;
            }
            return $this->desc("{$ns}{$class}", "#{$n->name}.");
        }

        if ($n instanceof Function_ && $n->name->toString() !== '') {
            $name = $n->namespacedName?->toString();
            if ($name === null || $name === '') {
                $name = $n->name->toString();
            }
            $name = str_replace('\\', '/', $name);
            return $this->desc($name, '().');
        }

        if ($n instanceof Name && $n->toString() !== '') {
            if ($n->toString() === 'self' || $n->toString() ===  'static') {
                $ns = $this->namespaceName($n);
                $class = $this->classLikeName($n);
                if ($class === null) {
                    return null;
                }
                return $this->desc("{$ns}{$class}", '#');
            }

            if ($n->toString() === 'parent') {
                $classLike = $this->classLike($n);
                if ($classLike === null) {
                    return null;
                }
                if ($classLike instanceof Class_) {
                    if ($classLike->extends === null) {
                        // Reference to parent in class without parent class.
                        return null;
                    }
                    return $this->name($classLike->extends);
                }
                if ($classLike instanceof Trait_) {
                    return null;
                }
                throw new LogicException('Reference to parent in unexpected node type: ' . $classLike::class . '.');
            }

            $name = $n->toString();
            $desc = str_replace('\\', '/', $name);

            if ($this->composer->isConst($name)) {
                return $this->desc($desc, '.');
            }
            if ($this->composer->isClassLike($name)) {
                return $this->desc($desc, '#');
            }
            if ($this->composer->isFunc($name)) {
                return $this->desc($desc, '().');
            }
            // TODO(drj): could be a constant
            return null;
        }

        if ($n instanceof Param && $n->var instanceof Variable && is_string($n->var->name) && $n->var->name !== '') {
            $name = $this->funcLikeName($n);
            if ($name === null) {
                return null;
            }
            return $this->nameParam($name, $n->var->name);
        }

        if ($n instanceof PropertyItem) {
            $ns = $this->namespaceName($n);
            $class = $this->classLikeName($n);
            if ($class === null) {
                return null;
            }
            return $this->desc("{$ns}{$class}", "#\${$n->name}.");
        }

        throw new LogicException('Unexpected node type: ' . $n::class . '.');
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

    /** @return ?non-empty-string */
    private function classLikeName(Node $n): ?string
    {
        $c = $this->classLike($n);
        if ($c === null) {
            return null;
        }
        $name = $c->name?->toString();
        if ($name === null || $name === '') {
            return "anon-class-{$c->getStartTokenPos()}";
        }
        return $name;
    }

    private function classLike(Node $n): ?ClassLike
    {
        while (true) {
            $n = $n->getAttribute('parent');
            if ($n === null) {
                return null;
            }
            if ($n instanceof ClassLike) {
                return $n;
            }
        }
    }

    /** @return ?non-empty-string */
    public function funcLikeName(Node $n): ?string
    {
        while (true) {
            $n = $n->getAttribute('parent');
            if ($n === null) {
                return null;
            }
            if ($n instanceof FunctionLike) {
                return $this->name($n);
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
