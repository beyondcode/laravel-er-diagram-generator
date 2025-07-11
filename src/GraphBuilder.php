<?php

namespace BeyondCode\ErdGenerator;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Schema;
use phpDocumentor\GraphViz\Graph;
use Illuminate\Support\Collection;
use phpDocumentor\GraphViz\Node;
use \Illuminate\Database\Eloquent\Model as EloquentModel;

class GraphBuilder
{
    /** @var Graph */
    private $graph;

    /**
     * @param $models
     * @return Graph
     */
    public function buildGraph(Collection $models) : Graph
    {
        $this->graph = new Graph();

        foreach (config('erd-generator.graph') as $key => $value) {
            $this->graph->{"set{$key}"}($value);
        }

        $this->addModelsToGraph($models);

        return $this->graph;
    }

    /**
     * Generate a structured text representation of the ER diagram
     *
     * @param Collection $models
     * @return string
     */
    public function generateStructuredTextRepresentation(Collection $models) : string
    {
        $output = "# Entity Relationship Diagram\n\n";

        // First list all models/entities with their attributes
        $output .= "## Entities\n\n";

        foreach ($models as $model) {
            /** @var Model $model */
            $eloquentModel = app($model->getModel());
            $output .= "### " . $model->getLabel() . " (`" . $model->getModel() . "`)\n\n";

            // Add table columns if available
            if (config('erd-generator.use_db_schema')) {
                $columns = $this->getTableColumnsFromModel($eloquentModel);
                if (count($columns) > 0) {
                    $output .= "#### Attributes:\n\n";
                    foreach ($columns as $column) {
                        if (is_object($column)) {
                            $name = $column->getName();
                            $typeName = $column->getType()->getName();
                        } else {
                            $name = $column['name'] ?? '';
                            $typeName = $column['type_name'] ?? '';
                        }
                        $columnType = config('erd-generator.use_column_types') ? ' (' . $typeName . ')' : '';
                        $output .= "- `" . $name . "`" . $columnType . "\n";
                    }
                    $output .= "\n";
                }
            }
        }

        // Then list all relationships
        $output .= "## Relationships\n\n";

        foreach ($models as $model) {
            /** @var Model $model */
            if (count($model->getRelations()) > 0) {
                $output .= "### " . $model->getLabel() . " Relationships\n\n";

                foreach ($model->getRelations() as $relation) {
                    /** @var ModelRelation $relation */
                    // Find the related model by comparing model class names
                    $relatedModelClass = $relation->getModel();

                    $relatedModel = $models->first(function ($m) use ($relatedModelClass) {
                        return $m->getModel() === $relatedModelClass;
                    });
                    if ($relatedModel) {
                        $output .= "- **" . $relation->getType() . "** `" . $relation->getName() . "` to " .
                                  $relatedModel->getLabel() . " (Local Key: `" . $relation->getLocalKey() .
                                  "`, Foreign Key: `" . $relation->getForeignKey() . "`)\n";
                    }
                }

                $output .= "\n";
            }
        }

        return $output;
    }

    protected function getTableColumnsFromModel(EloquentModel $model)
    {
        try {

            $table = $model->getConnection()->getTablePrefix() . $model->getTable();
            $schema = $model->getConnection()->getDoctrineSchemaManager($table);
            $databasePlatform = $schema->getDatabasePlatform();
            $databasePlatform->registerDoctrineTypeMapping('enum', 'string');

            $database = null;

            if (strpos($table, '.')) {
                list($database, $table) = explode('.', $table);
            }

            return $schema->listTableColumns($table, $database);
        } catch (\Throwable $e) {
        }

        try {
            return Schema::getColumns($model->getTable());
        } catch (\Throwable $e) {
        }

        return [];
    }

    protected function getModelLabel(EloquentModel $model, string $label)
    {

        $table = '<<table width="100%" height="100%" border="0" margin="0" cellborder="1" cellspacing="0" cellpadding="10">' . PHP_EOL;
        $table .= '<tr width="100%"><td width="100%" bgcolor="'.config('erd-generator.table.header_background_color').'"><font color="'.config('erd-generator.table.header_font_color').'">' . $label . '</font></td></tr>' . PHP_EOL;

        if (config('erd-generator.use_db_schema')) {
            $columns = $this->getTableColumnsFromModel($model);
            foreach ($columns as $column) {
                if (is_object($column)) {
                    $name = $column->getName();
                    $typeName = $column->getType()->getName();
                } else { // it's an array!
                    $name = $column['name'] ?? '';
                    $typeName = $column['type_name'] ?? '';
                }
                $label = $name;
                if (config('erd-generator.use_column_types')) {
                    $label .= ' ('. $typeName .')';
                }
                $table .= '<tr width="100%"><td port="' . $name . '" align="left" width="100%"  bgcolor="'.config('erd-generator.table.row_background_color').'"><font color="'.config('erd-generator.table.row_font_color').'" >' . $label . '</font></td></tr>' . PHP_EOL;
            }
        }

        $table .= '</table>>';

        return $table;
    }

    protected function addModelsToGraph(Collection $models)
    {
        // Add models to graph
        $models->map(function (Model $model) {
            $eloquentModel = app($model->getModel());
            $this->addNodeToGraph($eloquentModel, $model->getNodeName(), $model->getLabel());
        });

        // Create relations
        $models->map(function ($model) {
            $this->addRelationToGraph($model);
        });
    }

    protected function addNodeToGraph(EloquentModel $eloquentModel, string $nodeName, string $label)
    {
        $node = Node::create($nodeName);
        $node->setLabel($this->getModelLabel($eloquentModel, $label));

        foreach (config('erd-generator.node') as $key => $value) {
            $node->{"set{$key}"}($value);
        }

        $this->graph->setNode($node);
    }

    protected function addRelationToGraph(Model $model)
    {

        $modelNode = $this->graph->findNode($model->getNodeName());

        /** @var ModelRelation $relation */
        foreach ($model->getRelations() as $relation) {
            $relatedModelNode = $this->graph->findNode($relation->getModelNodeName());

            if ($relatedModelNode !== null) {
                $this->connectByRelation($model, $relation, $modelNode, $relatedModelNode);
            }
        }
    }

    /**
     * @param Node $modelNode
     * @param Node $relatedModelNode
     * @param ModelRelation $relation
     */
    protected function connectNodes(Node $modelNode, Node $relatedModelNode, ModelRelation $relation): void
    {
        $edge = Edge::create($modelNode, $relatedModelNode);
        $edge->setFromPort($relation->getLocalKey());
        $edge->setToPort($relation->getForeignKey());
        $edge->setLabel(' ');
        $edge->setXLabel($relation->getType() . PHP_EOL . $relation->getName());

        foreach (config('erd-generator.edge') as $key => $value) {
            $edge->{"set{$key}"}($value);
        }

        foreach (config('erd-generator.relations.' . $relation->getType(), []) as $key => $value) {
            $edge->{"set{$key}"}($value);
        }

        $this->graph->link($edge);
    }

    /**
     * @param Model $model
     * @param ModelRelation $relation
     * @param Node $modelNode
     * @param Node $relatedModelNode
     * @return void
     */
    protected function connectBelongsToMany(
        Model $model,
        ModelRelation $relation,
        Node $modelNode,
        Node $relatedModelNode
    ): void {
        $relationName = $relation->getName();
        $eloquentModel = app($model->getModel());

        /** @var BelongsToMany $eloquentRelation */
        $eloquentRelation = $eloquentModel->$relationName();

        if (!$eloquentRelation instanceof BelongsToMany) {
            return;
        }

        $pivotClass = $eloquentRelation->getPivotClass();

        try {
            /** @var EloquentModel $relationModel */
            $pivotModel = app($pivotClass);
            $pivotModel->setTable($eloquentRelation->getTable());
            $label = (new \ReflectionClass($pivotClass))->getShortName();
            $pivotTable = $eloquentRelation->getTable();
            $this->addNodeToGraph($pivotModel, $pivotTable, $label);

            $pivotModelNode = $this->graph->findNode($pivotTable);

            $relation = new ModelRelation(
                $relationName,
                'BelongsToMany',
                $model->getModel(),
                $eloquentRelation->getParent()->getKeyName(),
                $eloquentRelation->getForeignPivotKeyName()
            );

            $this->connectNodes($modelNode, $pivotModelNode, $relation);

            $relation = new ModelRelation(
                $relationName,
                'BelongsToMany',
                $model->getModel(),
                $eloquentRelation->getRelatedPivotKeyName(),
                $eloquentRelation->getRelated()->getKeyName()
            );

            $this->connectNodes($pivotModelNode, $relatedModelNode, $relation);
        } catch (\ReflectionException $e){}
    }

    /**
     * @param Model $model
     * @param ModelRelation $relation
     * @param Node $modelNode
     * @param Node $relatedModelNode
     */
    protected function connectByRelation(
        Model $model,
        ModelRelation $relation,
        Node $modelNode,
        Node $relatedModelNode
    ): void {

        if ($relation->getType() === 'BelongsToMany') {
            $this->connectBelongsToMany($model, $relation, $modelNode, $relatedModelNode);
            return;
        }

        $this->connectNodes($modelNode, $relatedModelNode, $relation);
    }
}
