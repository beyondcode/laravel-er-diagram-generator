<?php

namespace BeyondCode\ErdGenerator;

class ModelRelation
{
    private $type;
    private $model;
    private $localKey;
    private $foreignKey;
    private $name;

    public function __construct($name, $type, $model, $localKey = null, $foreignKey)
    {
        $this->type = $type;
        $this->model = $model;
        $this->localKey = $localKey;
        $this->foreignKey = $foreignKey;
        $this->name = $name;
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
    public function getModelNodeName()
    {
        return str_slug($this->model);
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return null
     */
    public function getLocalKey()
    {
        return $this->localKey;
    }

    /**
     * @return mixed
     */
    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }
}