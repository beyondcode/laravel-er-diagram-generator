<?php

namespace BeyondCode\ErdGenerator;

use Illuminate\Support\Collection;

class Model
{
    private $model;
    private $label;
    private $relations;

    public function __construct(string $model, string $label, Collection $relations)
    {
        $this->model = $model;
        $this->label = $label;
        $this->relations = $relations;
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return string
     */
    public function getNodeName()
    {
        return str_replace('-', '', str_slug($this->model));
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return Collection
     */
    public function getRelations()
    {
        return $this->relations;
    }
}