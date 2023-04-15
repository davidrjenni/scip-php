<?php

declare(strict_types=1);

namespace ScipPhp;

use PhpParser\Node;
use PhpParser\Node\Const_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\NullsafePropertyFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\EnumCase;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use Scip\Occurrence;
use Scip\SymbolInformation;
use Scip\SymbolRole;
use Scip\SyntaxKind;
use ScipPhp\Composer\Composer;
use ScipPhp\Parser\PosResolver;
use ScipPhp\Types\Types;

use function is_string;
use function str_starts_with;

final class DocIndexer
{
    private readonly DocGenerator $docGenerator;

    /** @var array<non-empty-string, SymbolInformation> */
    public array $symbols;

    /** @var array<non-empty-string, SymbolInformation> */
    public array $extSymbols;

    /** @var array<int, Occurrence> */
    public array $occurrences;

    public function __construct(
        private readonly Composer $composer,
        private readonly SymbolNamer $namer,
        private readonly Types $types,
    ) {
        $this->docGenerator = new DocGenerator();
        $this->symbols = [];
        $this->extSymbols = [];
        $this->occurrences = [];
    }

    public function index(PosResolver $pos, Node $n): void
    {
        // ------- Definitions -------

        if ($n instanceof ClassConst) {
            foreach ($n->consts as $c) {
                $this->def($pos, $c, $c->name, SyntaxKind::IdentifierConstant);
            }
            return;
        }
        if ($n instanceof ClassLike && $n->name !== null) {
            $this->def($pos, $n, $n->name, SyntaxKind::IdentifierType);
            return;
        }
        if ($n instanceof ClassMethod) {
            $this->def($pos, $n, $n->name);
            return;
        }
        if ($n instanceof Param && $n->var instanceof Variable && is_string($n->var->name)) {
            // Constructor property promotion.
            if ($n->flags !== 0) {
                $p = new PropertyProperty($n->var->name, $n->default, $n->getAttributes());
                $prop = new Property($n->flags, [$p], $n->getAttributes(), $n->type, $n->attrGroups);
                $p->setAttribute('parent', $prop);
                $this->def($pos, $p, $n->var, SyntaxKind::IdentifierParameter);
            }
            return;
        }
        if ($n instanceof Property) {
            foreach ($n->props as $p) {
                $this->def($pos, $p, $p->name);
            }
            return;
        }

        // ------- Usages -------

        if ($n instanceof ClassConstFetch && $n->name instanceof Identifier && $n->name->toString() !== '') {
            $symbol = $this->types->constDef($n->class, $n->name->toString());
            if ($symbol !== null) {
                $this->ref($pos, $symbol, $n->name, SyntaxKind::IdentifierConstant);
            }
            return;
        }
        if (
            ($n instanceof MethodCall || $n instanceof NullsafeMethodCall || $n instanceof StaticCall)
            && $n->name instanceof Identifier
            && $n->name->toString() !== ''
        ) {
            $symbol = $n instanceof StaticCall
                ? $this->types->methDef($n->class, $n->name->toString())
                : $this->types->methDef($n->var, $n->name->toString());
            if ($symbol !== null) {
                $this->ref($pos, $symbol, $n->name);
            }
            return;
        }
        if ($n instanceof Name) {
            if ($n->getAttribute('parent') instanceof Namespace_) {
                return;
            }
            $symbol = $this->types->nameDef($n);
            if ($symbol !== null) {
                $this->ref($pos, $symbol, $n);
            }
            return;
        }
        if (
            ($n instanceof PropertyFetch || $n instanceof NullsafePropertyFetch || $n instanceof StaticPropertyFetch)
            && $n->name instanceof Identifier
            && $n->name->toString() !== ''
        ) {
            $symbol = $n instanceof StaticPropertyFetch
                ? $this->types->propDef($n->class, $n->name->toString())
                : $this->types->propDef($n->var, $n->name->toString());
            if ($symbol !== null) {
                $this->ref($pos, $symbol, $n->name);
            }
            return;
        }
    }

    private function def(
        PosResolver $pos,
        Const_|ClassLike|ClassMethod|Param|PropertyProperty $n,
        Node $posNode,
        int $kind = SyntaxKind::Identifier,
    ): void {
        $symbol = $this->namer->name($n);
        if ($symbol === null) {
            return;
        }
        $doc = $this->docGenerator->create($n);
        $this->symbols[$symbol] = new SymbolInformation([
            'symbol'        => $symbol,
            'documentation' => $doc,
        ]);
        $this->occurrences[] = new Occurrence([
            'range'        => $pos->pos($posNode),
            'symbol'       => $symbol,
            'symbol_roles' => SymbolRole::Definition,
            'syntax_kind'  => $kind,
        ]);
    }

    /** @param  non-empty-string  $symbol */
    private function ref(
        PosResolver $pos,
        string $symbol,
        Node $posNode,
        int $kind = SyntaxKind::Identifier,
        int $role = SymbolRole::UnspecifiedSymbolRole,
    ): void {
        if (!str_starts_with($symbol, 'local ')) {
            $ident = $this->namer->extractIdent($symbol);
            if ($this->composer->isDependency($ident)) {
                $this->extSymbols[$symbol] = new SymbolInformation([
                    'symbol'        => $symbol,
                    'documentation' => [$symbol], // TODO(drj): build hover content
                ]);
            }
        }

        $this->occurrences[] = new Occurrence([
            'range'        => $pos->pos($posNode),
            'symbol'       => $symbol,
            'symbol_roles' => $role,
            'syntax_kind'  => $kind,
        ]);
    }
}
