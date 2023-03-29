<?php

declare(strict_types=1);

namespace Tests\File;

use PHPUnit\Framework\TestCase;
use ScipPhp\File\CannotReadFileException;
use ScipPhp\File\Reader;

use const DIRECTORY_SEPARATOR;

final class ReaderTest extends TestCase
{
    public function testRead(): void
    {
        $contents = Reader::read(__DIR__ . DIRECTORY_SEPARATOR . 'testdata' . DIRECTORY_SEPARATOR . 'test-file.txt');

        self::assertEquals("The quick brown fox jumps\nover the lazy dog", $contents);
    }

    public function testReadFails(): void
    {
        self::expectException(CannotReadFileException::class);
        self::expectExceptionMessage('Cannot read file: non-existent.txt');

        Reader::read('non-existent.txt');
    }
}
