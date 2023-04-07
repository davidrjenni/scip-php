<?php

declare(strict_types=1);

namespace ScipPhp\File;

use RuntimeException;

use function file_get_contents;

final class Reader
{
    /** @param  non-empty-string  $filename */
    public static function read(string $filename): string
    {
        $contents = @file_get_contents($filename);
        if ($contents === false) {
            throw new RuntimeException("Cannot read file: {$filename}.");
        }
        return $contents;
    }
}
