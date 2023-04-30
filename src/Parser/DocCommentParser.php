<?php

declare(strict_types=1);

namespace ScipPhp\Parser;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;

use function count;

final class DocCommentParser
{
    private readonly PhpDocParser $parser;

    private readonly Lexer $lexer;

    public function __construct()
    {
        $constExprParser = new ConstExprParser();
        $typeParser = new TypeParser($constExprParser);
        $this->parser = new PhpDocParser($typeParser, $constExprParser);
        $this->lexer = new Lexer();
    }

    public function parsePropertyType(Node $node): ?TypeNode
    {
        $doc = $node->getDocComment();
        if ($doc === null) {
            return null;
        }
        $n = $this->parse($doc);
        $tags = $n->getVarTagValues();
        if (count($tags) === 0) {
            return null;
        }
        return $tags[0]->type;
    }

    public function parseReturnType(Node $node): ?TypeNode
    {
        $doc = $node->getDocComment();
        if ($doc === null) {
            return null;
        }
        $n = $this->parse($doc);
        $tags = $n->getReturnTagValues();
        if (count($tags) === 0) {
            return null;
        }
        return $tags[0]->type;
    }

    private function parse(Doc $doc): PhpDocNode
    {
        $comment = $doc->getText();
        $tokens = $this->lexer->tokenize($comment);
        $iterator = new TokenIterator($tokens);
        return $this->parser->parse($iterator);
    }
}
