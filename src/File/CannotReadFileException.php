<?php

declare(strict_types=1);

namespace ScipPhp\File;

use Exception;

final class CannotReadFileException extends Exception
{
    /** @param  non-empty-string  $filename */
    public function __construct(string $filename)
    {
        parent::__construct("Cannot read file: {$filename}.");
    }
}
