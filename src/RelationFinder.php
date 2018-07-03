<?php

namespace BeyondCode\ErdGenerator;

use ReflectionClass;
use ReflectionMethod;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RelationFinder
{
    /**
     * Return all relations from a fully qualified model class name.
     *
     * @param string $model
     * @return Collection
     */
    public function getModelRelations(string $model)
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