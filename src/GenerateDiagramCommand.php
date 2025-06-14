<?php

namespace BeyondCode\ErdGenerator;

use BeyondCode\ErdGenerator\Model as GraphModel;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use phpDocumentor\GraphViz\Graph;
use ReflectionClass;

class GenerateDiagramCommand extends Command
{
    const FORMAT_TEXT = 'text';

    const DEFAULT_FILENAME = 'graph';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'generate:erd {filename?} {--format=png} {--text-output : Output as text file instead of image}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate ER diagram.';

    /** @var ModelFinder */
    protected $modelFinder;

    /** @var RelationFinder */
    protected $relationFinder;

    /** @var Graph */
    protected $graph;

    /** @var GraphBuilder */
    protected $graphBuilder;

    public function __construct(ModelFinder $modelFinder, RelationFinder $relationFinder, GraphBuilder $graphBuilder)
    {
        parent::__construct();

        $this->relationFinder = $relationFinder;
        $this->modelFinder = $modelFinder;
        $this->graphBuilder = $graphBuilder;
    }

    /**
     * @throws \phpDocumentor\GraphViz\Exception
     */
    public function handle()
    {
        $models = $this->getModelsThatShouldBeInspected();

        $this->info("Found {$models->count()} models.");
        $this->info("Inspecting model relations.");

        $bar = $this->output->createProgressBar($models->count());

        $models->transform(function ($model) use ($bar) {
            $bar->advance();
            return new GraphModel(
                $model,
                (new ReflectionClass($model))->getShortName(),
                $this->relationFinder->getModelRelations($model)
            );
        });

        $graph = $this->graphBuilder->buildGraph($models);

        // First check for text-output option
        if ($this->option('text-output') || $this->option('format') === self::FORMAT_TEXT) {
            $textOutput = $graph->__toString();
            
            // If text-output option is set, write to file
            if ($this->option('text-output')) {
                $outputFileName = $this->getTextOutputFileName();
                file_put_contents($outputFileName, $textOutput);
                $this->info(PHP_EOL);
                $this->info('Wrote text diagram to ' . $outputFileName);
                return;
            }
            
            // Otherwise just output to console
            $this->info($textOutput);
            return;
        }

        // Then check for .txt extension in filename
        $outputFileName = $this->getOutputFileName();
        if (pathinfo($outputFileName, PATHINFO_EXTENSION) === 'txt') {
            // Generate structured text output for .txt files
            $textOutput = $this->graphBuilder->generateStructuredTextRepresentation($models);
            file_put_contents($outputFileName, $textOutput);
            $this->info(PHP_EOL);
            $this->info('Wrote structured ER diagram to ' . $outputFileName);
            return;
        }

        $graph->export($this->option('format'), $outputFileName);

        $this->info(PHP_EOL);
        $this->info('Wrote diagram to ' . $outputFileName);
    }

    protected function getOutputFileName(): string
    {
        return $this->argument('filename') ?:
            static::DEFAULT_FILENAME . '.' . $this->option('format');
    }

    protected function getTextOutputFileName(): string
    {
        return $this->argument('filename') ?: static::DEFAULT_FILENAME . '.txt';
    }

    protected function getModelsThatShouldBeInspected(): Collection
    {
        $directories = config('erd-generator.directories');

        $modelsFromDirectories = $this->getAllModelsFromEachDirectory($directories);

        return $modelsFromDirectories;
    }

    protected function getAllModelsFromEachDirectory(array $directories): Collection
    {
        return collect($directories)
            ->map(function ($directory) {
                return $this->modelFinder->getModelsInDirectory($directory)->all();
            })
            ->flatten();
    }
}
