<?php

declare(strict_types=1);

namespace ScipPhp\Parser;

use InvalidArgumentException;
use PhpParser\Node;

use function strlen;
use function strrpos;

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
    public function pos(Node $n): array
    {
        return [
            $n->getStartLine() - 1,
            $this->toColumn($n->getStartFilePos()) - 1,
            $n->getEndLine() - 1,
            $this->toColumn($n->getEndFilePos()),
        ];
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
