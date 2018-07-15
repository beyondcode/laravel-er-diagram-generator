<?php

namespace BeyondCode\ErdGenerator;

use phpDocumentor\GraphViz\Graph;
use Illuminate\Support\Collection;
use phpDocumentor\GraphViz\Node;

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
            $this->graph->{"set${key}"}($value);
        }

        $this->addModelsToGraph($models);

        return $this->graph;
    }

    protected function getTableColumnsFromModel(string $model)
    {
        try {
            $model = app($model);

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

        return [];
    }

    protected function getModelLabel(string $model, string $label)
    {

        $table = '<<table width="100%" height="100%" border="0" margin="0" cellborder="1" cellspacing="0" cellpadding="10">' . PHP_EOL;
        $table .= '<tr width="100%"><td width="100%" bgcolor="'.config('erd-generator.table.header_background_color').'"><font color="'.config('erd-generator.table.header_font_color').'">' . $label . '</font></td></tr>' . PHP_EOL;

        if (config('erd-generator.use_db_schema')) {
            $columns = $this->getTableColumnsFromModel($model);
            foreach ($columns as $column) {
                $label = $column->getName();
                if (config('erd-generator.use_column_types')) {
                    $label .= ' ('.$column->getType()->getName().')';
                }
                $table .= '<tr width="100%"><td port="' . $column->getName() . '" align="left" width="100%"  bgcolor="'.config('erd-generator.table.row_background_color').'"><font color="'.config('erd-generator.table.row_font_color').'" >' . $label . '</font></td></tr>' . PHP_EOL;
            }
        }

        $table .= '</table>>';

        return $table;
    }

    protected function addModelsToGraph(Collection $models)
    {
        // Add models to graph
        $models->map(function (Model $model) {
            $this->addNodeToGraph($model->getModel(), $model->getNodeName(), $model->getLabel());
        });

        // Create relations
        $models->map(function ($model) {
            $this->addRelationToGraph($model);
        });
    }

    protected function addNodeToGraph(string $className, string $nodeName, string $label)
    {
        $node = Node::create($nodeName);
        $node->setLabel($this->getModelLabel($className, $label));

        foreach (config('erd-generator.node') as $key => $value) {
            $node->{"set${key}"}($value);
        }

        $this->graph->setNode($node);
    }

    protected function addRelationToGraph(Model $model)
    {

        $modelNode = $this->graph->findNode($model->getNodeName());

        foreach ($model->getRelations() as $relation) {
            $relatedModelNode = $this->graph->findNode($relation->getModelNodeName());

            if ($relatedModelNode !== null) {
                $edge = Edge::create($modelNode, $relatedModelNode);
                $edge->setFromPort($relation->getLocalKey());
                $edge->setToPort($relation->getForeignKey());
                $edge->setLabel(' ');
                $edge->setXLabel($relation->getType(). PHP_EOL . $relation->getName());

                foreach (config('erd-generator.edge') as $key => $value) {
                    $edge->{"set${key}"}($value);
                }

                foreach (config('erd-generator.relations.' . $relation->getType(), []) as $key => $value) {
                    $edge->{"set${key}"}($value);
                }

                $this->graph->link($edge);
            }
        }
    }
}