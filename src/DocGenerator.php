<?php

declare(strict_types=1);

namespace ScipPhp;

use LogicException;
use PhpParser\Comment\Doc;
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
use function preg_replace;
use function str_replace;

final class DocGenerator
{
    private readonly PrettyPrinter $printer;

    public function __construct()
    {
        $this->printer = new PrettyPrinter();
    }

    /** @return non-empty-array<int, non-empty-string> */
    public function create(Const_|ClassLike|ClassMethod|Param|PropertyProperty $n): array
    {
        ['sign' => $s, 'doc' => $doc] = $this->signature($n);
        $s = "```php\n{$s}\n```";
        if ($doc === '') {
            return [$s];
        }
        return [$s, $doc];
    }

    /** @return array{sign: non-empty-string, doc: string} */
    private function signature(Const_|ClassLike|ClassMethod|Param|PropertyProperty $n): array
    {
        if ($n instanceof Const_) {
            return $this->constSign($n);
        }
        if ($n instanceof Class_) {
            return $this->classSign($n);
        }
        if ($n instanceof Interface_) {
            return $this->interfaceSign($n);
        }
        if ($n instanceof Trait_) {
            $sign = "trait {$n->name}";
            $comment = $n->getDocComment();
            $doc = $this->cleanup($comment);
            return ['sign' => $sign, 'doc' => $doc];
        }
        if ($n instanceof Enum_) {
            $sign = "enum {$n->name}";
            $comment = $n->getDocComment();
            $doc = $this->cleanup($comment);
            return ['sign' => $sign, 'doc' => $doc];
        }
        if ($n instanceof ClassMethod) {
            return $this->methodSign($n);
        }
        if ($n instanceof Param) {
            $sign = $this->printer->prettyPrint([$n]);
            if ($sign === '') {
                throw new LogicException('Cannot pretty-print parameter.');
            }
            $comment = $n->getDocComment();
            $doc = $this->cleanup($comment);
            return ['sign' => $sign, 'doc' => $doc];
        }
        if ($n instanceof PropertyProperty) {
            return $this->propertySign($n);
        }

        throw new LogicException('Unexpected node type: ' . $n::class);
    }

    /** @return array{sign: non-empty-string, doc: string} */
    private function classSign(Class_ $c): array
    {
        $sign = "class {$c->name}";
        if ($c->isAbstract()) {
            $sign = "abstract {$sign}";
        } elseif ($c->isFinal()) {
            $sign = "final {$sign}";
        }
        if ($c->extends !== null) {
            $sign = "{$sign} extends {$c->extends}";
        }
        if (count($c->implements) > 0) {
            $sign = "{$sign} implements " . $this->printer->prettyPrint($c->implements);
        }

        $comment = $c->getDocComment();
        $doc = $this->cleanup($comment);

        return ['sign' => $sign, 'doc' => $doc];
    }

    /** @return array{sign: non-empty-string, doc: string} */
    private function interfaceSign(Interface_ $i): array
    {
        $sign = "interface {$i->name}";
        if (count($i->extends) > 0) {
            $sign = "{$sign} implements " . $this->printer->prettyPrint($i->extends);
        }

        $comment = $i->getDocComment();
        $doc = $this->cleanup($comment);

        return ['sign' => $sign, 'doc' => $doc];
    }

    /** @return array{sign: non-empty-string, doc: string} */
    private function constSign(Const_ $c): array
    {
        $sign = $this->printer->prettyPrint([$c]);
        if ($sign === '') {
            throw new LogicException('Cannot pretty-print constant.');
        }
        $classConst = $c->getAttribute('parent');
        if (!$classConst instanceof ClassConst) {
            return ['sign' => $sign, 'doc' => ''];
        }
        if ($classConst->isFinal()) {
            $sign = "final {$sign}";
        }
        $sign = $this->visibility($classConst) . " {$sign}";

        $comment = $classConst->getDocComment();
        $doc = $this->cleanup($comment);

        return ['sign' => $sign, 'doc' => $doc];
    }

    /** @return array{sign: non-empty-string, doc: string} */
    private function propertySign(PropertyProperty $p): array
    {
        $classProperty = $p->getAttribute('parent');
        if (!$classProperty instanceof Property) {
            // TODO(drj): side-effect of constructor property promotion.
            $sign = $this->printer->prettyPrint([$p]);
            if ($sign === '') {
                throw new LogicException('Cannot pretty-print property.');
            }
            return ['sign' => $sign, 'doc' => ''];
        }
        $modifiers = $this->visibility($classProperty);
        if ($classProperty->isStatic()) {
            $modifiers = "{$modifiers} static";
        }
        if ($classProperty->isReadonly()) {
            $modifiers = "{$modifiers} readonly";
        }

        $sign = $classProperty->type !== null
            ? "{$modifiers} " . $this->printer->prettyPrint([$classProperty->type]) . " \${$p->name}"
            : "{$modifiers} \${$p->name}";

        if ($p->default !== null) {
            $sign = "{$sign} = " . $this->printer->prettyPrint([$p->default]);
        }

        $comment = $classProperty->getDocComment();
        $doc = $this->cleanup($comment);

        return ['sign' => $sign, 'doc' => $doc];
    }

    /** @return array{sign: non-empty-string, doc: string} */
    private function methodSign(ClassMethod $m): array
    {
        $modifiers = $this->visibility($m);
        if ($m->isStatic()) {
            $modifiers = "{$modifiers} static";
        }
        if ($m->isFinal()) {
            $modifiers = "{$modifiers} final";
        } elseif ($m->isAbstract()) {
            $modifiers = "{$modifiers} abstract";
        }

        $sign = "{$modifiers} function {$m->name}(";
        foreach ($m->params as $i => $param) {
            if ($i > 0) {
                $sign .= ', ';
            }
            $sign .= $this->printer->prettyPrint([$param]);
        }

        $sign = $m->returnType !== null
            ? "{$sign}): " . $this->printer->prettyPrint([$m->returnType])
            : "{$sign})";

        $comment = $m->getDocComment();
        $doc = $this->cleanup($comment);

        return ['sign' => $sign, 'doc' => $doc];
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

    private function cleanup(?Doc $doc): string
    {
        $comment = $doc?->getText() ?? '';
        $comment = $this->remove('(^(\s+)?/\*\*\s)m', $comment);
        $comment = $this->remove('(^(\s+)?\*\s)m', $comment, -1);
        $comment = $this->remove('(^(\s+)?\*/)m', $comment);
        $comment = $this->remove('((\s+)?\*/$)m', $comment);
        return str_replace("\n", '<br>', $comment);
    }

    private function remove(string $pattern, string $subject, int $limit = 1): string
    {
        return preg_replace($pattern, '', $subject, $limit) ?? $subject;
    }
}
