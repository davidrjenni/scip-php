<?php

declare(strict_types=1);

namespace ScipPhp\File;

use function file_get_contents;
use function is_file;

final class Reader
{
    /** @param  non-empty-string  $filename */
    public static function read(string $filename): string
    {
        if (!is_file($filename)) {
            throw new CannotReadFileException($filename);
        }
        $contents = file_get_contents($filename);
        if ($contents === false) {
            throw new CannotReadFileException($filename);
        }
        return $contents;
    }
}
