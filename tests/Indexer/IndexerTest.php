<?php

declare(strict_types=1);

namespace Tests\Indexer;

use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ScipPhp\File\Reader;
use ScipPhp\Indexer;
use SplFileInfo;

use function array_keys;
use function exec;
use function file_put_contents;
use function is_file;
use function rename;
use function strlen;
use function substr;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

use const DIRECTORY_SEPARATOR;

final class IndexerTest extends TestCase
{
    private const TESTDATA_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'testdata' . DIRECTORY_SEPARATOR;

    /** @var non-empty-string */
    private string $indexFile;

    protected function setUp(): void
    {
        parent::setUp();

        $tempDir = sys_get_temp_dir();
        $indexFile = tempnam($tempDir, 'scip-php-index-');
        if ($indexFile === false || !is_file($indexFile)) {
            self::fail('Cannot create temporary file.');
        }
        if (!rename($indexFile, "{$indexFile}.scip")) {
            unlink($indexFile);
            self::fail('Cannot rename temporary file.');
        }
        $this->indexFile = "{$indexFile}.scip";
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (is_file($this->indexFile)) {
            unlink($this->indexFile);
        }
    }

    #[RunInSeparateProcess]
    public function testIndexer(): void
    {
        $indexer = new Indexer(self::TESTDATA_DIR . 'scip-php-test', 'test', []);
        $index = $indexer->index();

        file_put_contents($this->indexFile, $index->serializeToString());

        $actualPath = self::TESTDATA_DIR . 'actual';

        $output = [];
        $result = exec("scip snapshot --from {$this->indexFile} --to {$actualPath}", $output);
        if ($result === false) {
            self::fail('Error executing scip: ' . ($output[0] ?? 'no output') . '.');
        }

        $goldenFiles = self::files(self::TESTDATA_DIR . 'golden');
        $actualFiles = self::files($actualPath);

        self::assertSame(array_keys($goldenFiles), array_keys($actualFiles));

        foreach ($goldenFiles as $name => $goldenPath) {
            $actualPath = $actualFiles[$name];

            $golden = Reader::read($goldenPath);
            $actual = Reader::read($actualPath);

            self::assertSame($golden, $actual);
        }
    }

    /** @return array<non-empty-string, non-empty-string> */
    private static function files(string $dir): array
    {
        $fileIterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir),
        );

        $files = [];
        foreach ($fileIterator as $f) {
            if ($f instanceof SplFileInfo && $f->isFile() && $f->getExtension() === 'php') {
                $path = $f->getRealPath();
                $relPath = substr($f->getPathname(), strlen($dir));
                if ($path !== false && $path !== '' && $relPath !== '') {
                    $files[$relPath] = $path;
                }
            }
        }
        return $files;
    }
}
