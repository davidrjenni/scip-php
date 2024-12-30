<?php

declare(strict_types=1);

namespace ScipPhp\Parser;

use InvalidArgumentException;
use PhpParser\Comment;
use PhpParser\Node;
use RuntimeException;

use function explode;
use function preg_match;
use function preg_quote;
use function strlen;
use function strpos;
use function strrpos;
use function substr_count;

final readonly class PosResolver
{
    private int $codeLen;

    /** @param  non-empty-string  $code */
    public function __construct(private string $code)
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
    public function pos(Node|Comment $n): array
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
    public static function posInDoc(string $doc, int $startLine, string $tagName, string $name): array
    {
        $quotedName = preg_quote($name);
        $pattern = "/^\s*(\/)?\*+\s*{$tagName}[\s\S]*{$quotedName}($|\(|\s+)/m";
        if (preg_match($pattern, $doc) !== 1) {
            throw new RuntimeException("Cannot find {$tagName} {$name} in doc comment: {$doc}.");
        }

        $index = strpos($doc, $name);
        if ($index === false) {
            throw new RuntimeException("Cannot find {$tagName} {$name} in doc comment: {$doc}.");
        }

        $line = substr_count($doc, "\n", 0, $index);
        $lines = explode("\n", $doc);

        $startColumn = strpos($lines[$line] ?? '', $name);
        if ($startColumn === false) {
            throw new RuntimeException("Cannot find {$tagName} {$name} on line {$line} in doc comment: {$doc}.");
        }
        $endColumn = $startColumn + strlen($name);
        $startLine += $line;

        return [$startLine, $startColumn, $startLine, $endColumn];
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
