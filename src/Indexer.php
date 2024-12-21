<?php

declare(strict_types=1);

namespace ScipPhp;

use Scip\Document;
use Scip\Index;
use Scip\Language;
use Scip\Metadata;
use Scip\PositionEncoding;
use Scip\TextEncoding;
use Scip\ToolInfo;
use ScipPhp\Composer\Composer;
use ScipPhp\Parser\Parser;
use ScipPhp\Types\Types;

use function array_values;
use function str_replace;

final class Indexer
{
    private readonly Metadata $metadata;

    private readonly Parser $parser;

    private readonly Composer $composer;

    private readonly SymbolNamer $namer;

    private readonly Types $types;

    /**
     * @param  non-empty-string              $projectRoot
     * @param  non-empty-string              $version
     * @param  array<int, non-empty-string>  $args
     */
    public function __construct(
        private readonly string $projectRoot,
        string $version,
        array $args,
    ) {
        $this->metadata = new Metadata([
            'version'                => 1,
            'project_root'           => "file://{$projectRoot}",
            'text_document_encoding' => TextEncoding::UTF8,
            'tool_info'              => new ToolInfo([
                'name'      => 'scip-php',
                'version'   => $version,
                'arguments' => $args,
            ]),
        ]);

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
                'language'          => Language::PHP,
                'relative_path'     => str_replace($this->projectRoot . '/', '', $filename),
                'occurrences'       => $indexer->occurrences,
                'symbols'           => array_values($indexer->symbols),
                'position_encoding' => PositionEncoding::UTF8CodeUnitOffsetFromLineStart,
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
