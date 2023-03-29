<?php

declare(strict_types=1);

namespace ScipPhp\Parser;

use Exception;

final class CannotParseFileException extends Exception
{
    /** @param  non-empty-string  $filename */
    public function __construct(string $filename)
    {
        parent::__construct("Cannot parse file: {$filename}.");
    }
}
