<?php

namespace BeyondCode\ErdGenerator;

use BeyondCode\ErdGenerator\Model as GraphModel;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use phpDocumentor\GraphViz\Graph;
use ReflectionClass;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class GenerateDiagramCommand extends Command
{
    const FORMAT_TEXT = 'text';

    const DEFAULT_FILENAME = 'graph';

    /**
     * @var string
     */
    protected $modelNamespace = 'Modules\{#}\Entities\{#}';

    /**
     * @var string
     */
    protected $modulePath = 'Modules/{#}';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'generate:model-erd {models} {filename?} {--format=png}';

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

    /**
     * GenerateDiagramCommand constructor.
     *
     * @param ModelFinder $modelFinder
     * @param RelationFinder $relationFinder
     * @param GraphBuilder $graphBuilder
     */
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

        if ($this->option('format') === self::FORMAT_TEXT) {
            $this->info($graph->__toString());
            return;
        }

        $graph->export($this->option('format'), $this->getOutputFileName());

        $this->info(PHP_EOL);
        $this->info('Wrote diagram to ' . $this->getOutputFileName());
    }

    /**
     * @return string
     */
    protected function getOutputFileName(): string
    {
        return $this->argument('filename') ?:
            static::DEFAULT_FILENAME . '.' . $this->option('format');
    }

    /**
     * @param array $directories
     * @return Collection
     */
    protected function getAllModelsFromEachDirectory(array $directories): Collection
    {
        return collect($directories)
            ->map(function ($directory) {
                return $this->modelFinder->getModelsInDirectory($directory)->all();
            })
            ->flatten();
    }

    /**
     * @return Collection
     */
    protected function getModelsThatShouldBeInspected(): Collection
    {
        $models = explode(',', $this->argument('models'));

        $modulePaths = [];
        $modelNamespaces = [];
        foreach ($models as $model) {
            $path = explode('::', $model);
            $modelNamespaces[] = Str::replaceArray('{#}', $path, $this->modelNamespace);
            $modulePaths[] = Str::replaceArray('{#}', $path, $this->modulePath);
        }

        $directories = array_values(array_filter($modulePaths));
        $modelsFromDirectories = $this->getAllModelsFromEachDirectory($directories)->intersect($modelNamespaces)
            ->values();

        foreach ($modelsFromDirectories as $modelsFromDirectory) {
            $reflector = new \ReflectionClass($modelsFromDirectory);
            $catFile = file_get_contents($reflector->getFileName());
            preg_match_all('/(\w+)::class/', $catFile, $classRelations);
            preg_match_all('/Modules\\\\(\w+)\\\\Entities\\\\(\w+)/', $catFile, $namespaceRelations);
            $relationsFromNamespaces = Arr::last($namespaceRelations);
            $namespaces = array_combine($relationsFromNamespaces, Arr::first($namespaceRelations));
            $relations = Arr::last($classRelations);

            foreach ($relations as $relation) {
                if (! in_array($relation, $relationsFromNamespaces)) {
                    $baseDir = substr($modelsFromDirectory, 0, strrpos($modelsFromDirectory, "\\"));
                    $modelsFromDirectories->push($baseDir .'\\' . $relation);
                }
            }

            foreach ($namespaces as $name => $namespace) {
                if (in_array($name, $relations)) {
                    $modelsFromDirectories->push($namespace);
                }
            }
        }

        return $modelsFromDirectories;
    }
}
