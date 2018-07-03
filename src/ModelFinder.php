<?php

namespace BeyondCode\ErdGenerator;

use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\Node\Stmt\Class_;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use PhpParser\NodeVisitor\NameResolver;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class ModelFinder
{

    /** @var Filesystem */
    protected $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function getModelsInDirectory(string $directory): Collection
    {
        return Collection::make($this->filesystem->files($directory))->map(function ($path) {
            return $this->getFullyQualifiedClassNameFromFile($path);
        })->filter(function (string $className) {
            return !empty($className);
        })->filter(function (string $className) {
            return is_subclass_of($className, EloquentModel::class);
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
    
}