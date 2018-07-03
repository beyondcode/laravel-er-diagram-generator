<?php

namespace BeyondCode\ErdGenerator;

use ReflectionClass;
use ReflectionMethod;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\Node\Stmt\Class_;
use Illuminate\Console\Command;
use phpDocumentor\GraphViz\Graph;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Model;
use PhpParser\NodeVisitor\NameResolver;
use BeyondCode\ErdGenerator\Model as GraphModel;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GenerateDiagramCommand extends Command
{
    const FORMAT_TEXT = 'text';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'generate:erd {filename=graph.png} {--format=png}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate ER diagram.';

    /** @var Filesystem */
    protected $filesystem;

    /** @var Graph */
    protected $graph;

    /** @var GraphBuilder */
    protected $graphBuilder;

    public function __construct(Filesystem $filesystem, GraphBuilder $graphBuilder)
    {
        parent::__construct();

        $this->filesystem = $filesystem;
        $this->graphBuilder = $graphBuilder;
    }

    public function handle()
    {
        $models = $this->getModelsThatShouldBeInspected();

        $this->info("Found {$models->count()} models.");
        $this->info("Inspecing model relations.");

        $bar = $this->output->createProgressBar($models->count());

        $models->transform(function ($model) use ($bar) {
            $bar->advance();
            return new GraphModel(
                $model,
                (new ReflectionClass($model))->getShortName(),
                $this->getModelRelations($model)
            );
        });

        $graph = $this->graphBuilder->buildGraph($models);

        if ($this->option('format') === self::FORMAT_TEXT) {
            $this->info($graph->__toString());
            return;
        }

        $graph->export($this->option('format'), $this->argument('filename'));

        $this->info(PHP_EOL);
        $this->info('Wrote diagram to '.$this->argument('filename'));
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
                return $this->getModelInstancesInDirectory($directory)->all();
            })
            ->flatten();
    }

    protected function getModelInstancesInDirectory(string $directory): Collection
    {
        return collect($this->filesystem->files($directory))->map(function ($path) {
            return $this->getFullyQualifiedClassNameFromFile($path);
        })->filter(function (string $className) {
            return !empty($className);
        })->filter(function (string $className) {
            return is_subclass_of($className, Model::class);
        });
    }

    protected function getFullyQualifiedClassNameFromFile(string $path): string
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());

        $code = file_get_contents($path);

        $statements = $parser->parse($code);

        $statements = $traverser->traverse($statements);

        return collect($statements[0]->stmts)
                ->filter(function ($statement) {
                    return $statement instanceof Class_;
                })
                ->map(function (Class_ $statement) {
                    return $statement->namespacedName->toString();
                })
                ->first() ?? '';
    }

    protected function getModelRelations($model)
    {
        $class = new ReflectionClass($model);

        $traitMethods = Collection::make($class->getTraits())->map(function ($trait) {
            return Collection::make($trait->getMethods(ReflectionMethod::IS_PUBLIC));
        })->flatten();

        $methods = Collection::make($class->getMethods(ReflectionMethod::IS_PUBLIC))
            ->merge($traitMethods)
            ->reject(function (ReflectionMethod $method) use ($model) {
                return $method->class !== $model;
            })->reject(function (ReflectionMethod $method) use ($model) {
                return $method->getNumberOfParameters() > 0;
            });

        $relations = Collection::make();

        $methods->map(function (ReflectionMethod $method) use ($model, &$relations) {
            $relations = $relations->merge($this->getRelationshipFromMethodAndModel($method, $model));
        });

        $relations = $relations->filter();

        return $relations;
    }

    protected function getParentKey(string $qualifiedKeyName)
    {
        $segments = explode('.', $qualifiedKeyName);

        return end($segments);
    }

    protected function getRelationshipFromMethodAndModel(ReflectionMethod $method, string $model)
    {
        try {
            $return = $method->invoke(app($model));

            if ($return instanceof Relation) {
                $localKey = null;
                $foreignKey = null;

                if ($return instanceof HasOneOrMany) {
                    $localKey = $this->getParentKey($return->getQualifiedParentKeyName());
                    $foreignKey = $return->getForeignKeyName();
                }

                if ($return instanceof BelongsTo) {
                    $foreignKey = $this->getParentKey($return->getQualifiedOwnerKeyName());
                    $localKey = $return->getForeignKey();
                }

                if ($return instanceof BelongsToMany && ! $return instanceof MorphToMany) {
                    $foreignKey = $this->getParentKey($return->getQualifiedOwnerKeyName());
                    $localKey = $return->getForeignKey();
                }

                return [
                    $method->getName() => new ModelRelation(
                        $method->getShortName(),
                        (new ReflectionClass($return))->getShortName(),
                        (new ReflectionClass($return->getRelated()))->getName(),
                        $localKey,
                        $foreignKey
                    )
                ];
            }
        } catch (\Throwable $e) {}
        return null;
    }
}