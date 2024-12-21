<?php

declare(strict_types=1);

namespace ScipPhp\Parser;

use Closure;
use Override;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser as PhpParser;
use PhpParser\ParserFactory;
use RuntimeException;
use ScipPhp\File\Reader;

final class Parser
{
    private readonly ParentConnectingVisitor $parentConnectingVisitor;

    private readonly NameResolver $nameResolver;

    private readonly PhpParser $parser;

    public function __construct()
    {
        $this->parentConnectingVisitor = new ParentConnectingVisitor();
        $this->nameResolver = new NameResolver();
        $this->parser = (new ParserFactory())->createForNewestSupportedVersion();
    }

    /**
     * @param  non-empty-string  $filename
     * @param  Closure(PosResolver, Node): void  $visitor
     */
    public function traverse(string $filename, object $newThis, Closure $visitor): void
    {
        $code = Reader::read($filename);
        if ($code === '') {
            throw new RuntimeException("Cannot parse file: {$filename}.");
        }

        $stmts = $this->parser->parse($code);
        if ($stmts === null) {
            throw new RuntimeException("Cannot parse file: {$filename}.");
        }

        $pos = new PosResolver($code);

        $t = new NodeTraverser(
            $this->nameResolver,
            $this->parentConnectingVisitor,
            new class ($pos, $newThis, $visitor) extends NodeVisitorAbstract
            {
                public function __construct(
                    private readonly PosResolver $pos,
                    private readonly object $newThis,
                    private readonly Closure $visitor,
                ) {
                }

                #[Override]
                public function leaveNode(Node $n): ?Node
                {
                    $this->visitor->call($this->newThis, $this->pos, $n);
                    return null;
                }
            },
        );

        $t->traverse($stmts);
    }
}
