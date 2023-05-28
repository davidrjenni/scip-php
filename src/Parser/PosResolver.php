<?php

declare(strict_types=1);

namespace ScipPhp\Parser;

use InvalidArgumentException;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use RuntimeException;

use function explode;
use function preg_match;
use function preg_quote;
use function strlen;
use function strpos;
use function strrpos;

final class PosResolver
{
    private readonly int $codeLen;

    /** @param  non-empty-string  $code */
    public function __construct(private readonly string $code)
    {
        $this->codeLen = strlen($code);
    }

    /**
     * Resolves the position of the node in the format
     * [startLine, startCharacter, endLine, endCharacter].
     * As defined by the SCIP schema, line numbers and characters are 0-based.
     *
     * @return array{int, int, int, int}
     */
    public function pos(Node $n): array
    {
        return [
            $n->getStartLine() - 1,
            $this->toColumn($n->getStartFilePos()) - 1,
            $n->getEndLine() - 1,
            $this->toColumn($n->getEndFilePos()),
        ];
    }

    /**
     * @param  non-empty-string  $tagName
     * @param  non-empty-string  $name
     * @return array{int, int, int, int}
     */
    public static function posInDoc(Doc $doc, string $tagName, string $name): array
    {
        $startLine = $doc->getStartLine() - 1;
        $quotedName = preg_quote($name);
        $pattern = "/^\s*(\/)?\*+\s*{$tagName}.*\s+{$quotedName}($|\(|\s+\*\/)/m";
        $lines = explode("\n", $doc->getText());
        foreach ($lines as $line) {
            if (preg_match($pattern, $line) === 1) {
                $startColumn = strpos($line, $name);
                if ($startColumn === false) {
                    throw new RuntimeException("Cannot find {$tagName} {$name} in doc comment: {$line}.");
                }
                $endColumn = $startColumn + strlen($name);
                return [$startLine, $startColumn, $startLine, $endColumn];
            }
            $startLine++;
        }

        throw new RuntimeException("Cannot find {$tagName} {$name} in doc comment: {$doc->getText()}.");
    }

    private function toColumn(int $filePos): int
    {
        $offset = $filePos - $this->codeLen;
        if ($offset > 0) {
            throw new InvalidArgumentException('Invalid position information.');
        }

        $lineStartPos = strrpos($this->code, "\n", $offset);
        if ($lineStartPos === false) {
            $lineStartPos = -1;
        }
        return $filePos - $lineStartPos;
    }
}
