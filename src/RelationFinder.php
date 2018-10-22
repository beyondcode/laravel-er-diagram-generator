<?php

namespace BeyondCode\ErdGenerator;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionMethod;

class RelationFinder
{
    /**
     * Return all relations from a fully qualified model class name.
     *
     * @param string $model
     * @return Collection
     * @throws \ReflectionException
     */
    public function getModelRelations(string $model)
    {
        $class = new ReflectionClass($model);

        $traitMethods = Collection::make($class->getTraits())->map(function (ReflectionClass $trait) {
            return Collection::make($trait->getMethods(ReflectionMethod::IS_PUBLIC));
        })->flatten();

        $methods = Collection::make($class->getMethods(ReflectionMethod::IS_PUBLIC))
            ->merge($traitMethods)
            ->reject(function (ReflectionMethod $method) use ($model) {
                return $method->class !== $model || $method->getNumberOfParameters() > 0;
            });

        $relations = Collection::make();

        $methods->map(function (ReflectionMethod $method) use ($model, &$relations) {
            $relations = $relations->merge($this->getRelationshipFromMethodAndModel($method, $model));
        });

        $relations = $relations->filter();

        if ($ignoreRelations = array_get(config('erd-generator.ignore', []),$model))
        {
            $relations = $relations->diffKeys(array_flip($ignoreRelations));
        }

        return $relations;
    }

    /**
     * @param string $qualifiedKeyName
     * @return mixed
     */
    protected function getParentKey(string $qualifiedKeyName)
    {
        $segments = explode('.', $qualifiedKeyName);

        return end($segments);
    }

    /**
     * @param ReflectionMethod $method
     * @param string $model
     * @return array|null
     */
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