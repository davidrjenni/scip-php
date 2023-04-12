<?php

declare(strict_types=1);

namespace ScipPhp;

use LogicException;
use PhpParser\Node\Const_;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\PrettyPrinter\Standard as PrettyPrinter;

use function count;

final class DocGenerator
{
    private readonly PrettyPrinter $printer;

    public function __construct()
    {
        $this->printer = new PrettyPrinter();
    }

    /** @return non-empty-string */
    public function create(Const_|ClassLike|ClassMethod|Param|PropertyProperty $n): string
    {
        $s = $this->signature($n);
        return "```php\n{$s}\n```";
    }

    /** @return non-empty-string */
    private function signature(Const_|ClassLike|ClassMethod|Param|PropertyProperty $n): string
    {
        if ($n instanceof Const_) {
            return $this->constInfo($n);
        }
        if ($n instanceof Class_) {
            return $this->classInfo($n);
        }
        if ($n instanceof Interface_) {
            return $this->interfaceInfo($n);
        }
        if ($n instanceof Trait_) {
            return "trait {$n->name}";
        }
        if ($n instanceof Enum_) {
            return "enum {$n->name}";
        }
        if ($n instanceof ClassMethod) {
            return $this->methodInfo($n);
        }
        if ($n instanceof Param) {
            $s = $this->printer->prettyPrint([$n]);
            if ($s === '') {
                throw new LogicException('Cannot pretty-print parameter.');
            }
            return $s;
        }
        if ($n instanceof PropertyProperty) {
            return $this->propertyInfo($n);
        }

        throw new LogicException('Unexpected node type: ' . $n::class);
    }

    /** @return non-empty-string */
    private function classInfo(Class_ $class): string
    {
        $info = "class {$class->name}";

        if ($class->isAbstract()) {
            $info = "abstract {$info}";
        } elseif ($class->isFinal()) {
            $info = "final {$info}";
        }
        if ($class->extends !== null) {
            $info .= " extends {$class->extends}";
        }

        return count($class->implements) > 0
            ? "{$info} implements " . $this->printer->prettyPrint($class->implements)
            : $info;
    }

    /** @return non-empty-string */
    private function interfaceInfo(Interface_ $interface): string
    {
        $info = "interface {$interface->name}";
        return count($interface->extends) > 0
            ? "{$info} implements " . $this->printer->prettyPrint($interface->extends)
            : $info;
    }

    /** @return non-empty-string */
    private function constInfo(Const_ $const): string
    {
        $info = $this->printer->prettyPrint([$const]);
        if ($info === '') {
            throw new LogicException('Cannot pretty-print constant.');
        }
        $classConst = $const->getAttribute('parent');
        if (!$classConst instanceof ClassConst) {
            return $info;
        }
        if ($classConst->isFinal()) {
            $info = "final {$info}";
        }
        return $this->visibility($classConst) . " $info";
    }

    /** @return non-empty-string */
    private function propertyInfo(PropertyProperty $property): string
    {
        $classProperty = $property->getAttribute('parent');
        if (!$classProperty instanceof Property) {
            // TODO(drj): side-effect of constructor property promotion.
            $info = $this->printer->prettyPrint([$property]);
            if ($info === '') {
                throw new LogicException('Cannot pretty-print property.');
            }
            return $info;
        }
        $modifiers = $this->visibility($classProperty);
        if ($classProperty->isStatic()) {
            $modifiers = "{$modifiers} static";
        }
        if ($classProperty->isReadonly()) {
            $modifiers = "{$modifiers} readonly";
        }

        $info = $classProperty->type !== null
            ? "{$modifiers} " . $this->printer->prettyPrint([$classProperty->type]) . " \${$property->name}"
            : "{$modifiers} \${$property->name}";

        return $property->default !== null
            ? "{$info} = " . $this->printer->prettyPrint([$property->default])
            : $info;
    }

    /** @return non-empty-string */
    private function methodInfo(ClassMethod $method): string
    {
        $modifiers = $this->visibility($method);
        if ($method->isStatic()) {
            $modifiers = "{$modifiers} static";
        }
        if ($method->isFinal()) {
            $modifiers = "{$modifiers} final";
        } elseif ($method->isAbstract()) {
            $modifiers = "{$modifiers} abstract";
        }

        $info = "{$modifiers} function {$method->name}(";
        foreach ($method->params as $i => $param) {
            if ($i > 0) {
                $info .= ', ';
            }
            $info .= $this->printer->prettyPrint([$param]);
        }

        return $method->returnType !== null
            ? "{$info}): " . $this->printer->prettyPrint([$method->returnType])
            : "{$info})";
    }

    private function visibility(ClassConst|Property|ClassMethod $n): string
    {
        if ($n->isPrivate()) {
            return 'private';
        }
        if ($n->isProtected()) {
            return 'protected';
        }
        if ($n->isPublic()) {
            return 'public';
        }
        return '';
    }
}
