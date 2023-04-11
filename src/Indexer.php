<?php

declare(strict_types=1);

namespace ScipPhp;

use Scip\Document;
use Scip\Index;
use Scip\Language;
use Scip\Metadata;
use ScipPhp\Composer\Composer;
use ScipPhp\Parser\Parser;
use ScipPhp\Types\Types;

use function array_values;
use function str_replace;

final class Indexer
{
    private readonly Parser $parser;

    private readonly Composer $composer;

    private readonly SymbolNamer $namer;

    private readonly Types $types;

    /** @param  non-empty-string  $projectRoot */
    public function __construct(
        private readonly string $projectRoot,
        private readonly Metadata $metadata,
    ) {
        $this->parser = new Parser();
        $this->composer = new Composer($this->projectRoot);
        $this->namer = new SymbolNamer($this->composer);
        $this->types = new Types($this->composer, $this->namer);
    }

    public function index(): Index
    {
        $projectFiles = $this->composer->projectFiles();
        $this->types->collect(...$projectFiles);

        $documents = [];
        $extSymbols = [];
        foreach ($projectFiles as $filename) {
            $indexer = new DocIndexer($this->composer, $this->namer, $this->types);
            $this->parser->traverse($filename, $indexer, $indexer->index(...));
            $documents[] = new Document([
                'language'      => Language::PHP,
                'relative_path' => str_replace($this->projectRoot . '/', '', $filename),
                'occurrences'   => $indexer->occurrences,
                'symbols'       => array_values($indexer->symbols),
            ]);
            foreach ($indexer->extSymbols as $symbol => $info) {
                $extSymbols[$symbol] = $info;
            }
        }

        return new Index([
            'documents'        => $documents,
            'metadata'         => $this->metadata,
            'external_symbols' => $extSymbols,
        ]);
    }
}
