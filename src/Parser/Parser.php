<?php

declare(strict_types=1);

namespace ScipPhp\Parser;

use Closure;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser as PhpParser;
use PhpParser\ParserFactory;
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
        $this->parser = (new ParserFactory())->create(
            ParserFactory::ONLY_PHP7,
            new Lexer(
                [
                    'usedAttributes' => [
                        'comments',
                        'startLine',
                        'endLine',
                        'startTokenPos',
                        'endTokenPos',
                        'startFilePos',
                        'endFilePos',
                    ],
                ],
            ),
        );
    }

    /**
     * @param  non-empty-string  $filename
     * @param  Closure(PosResolver, Node): void  $visitor
     */
    public function traverse(string $filename, object $newThis, Closure $visitor): void
    {
        $code = Reader::read($filename);
        if ($code === '') {
            throw new CannotParseFileException($filename);
        }

        $stmts = $this->parser->parse($code);
        if ($stmts === null) {
            throw new CannotParseFileException($filename);
        }

        $pos = new PosResolver($code);

        $t = new NodeTraverser();
        $t->addVisitor($this->nameResolver);
        $t->addVisitor($this->parentConnectingVisitor);
        $t->addVisitor(
            new class ($pos, $newThis, $visitor) extends NodeVisitorAbstract
            {
                public function __construct(
                    private readonly PosResolver $pos,
                    private readonly object $newThis,
                    private readonly Closure $visitor,
                ) {
                }

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
