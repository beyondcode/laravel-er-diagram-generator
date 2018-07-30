<?php

namespace BeyondCode\ErdGenerator;

use phpDocumentor\GraphViz\Node;

class Edge extends \phpDocumentor\GraphViz\Edge
{
    protected $fromPort = null;

    protected $toPort = null;

    /**
     * @param Node $from
     * @param Node $to
     * @return Edge|\phpDocumentor\GraphViz\Edge
     */
    public static function create(Node $from, Node $to) {
        return new static($from, $to);
    }

    /**
     * @param null $fromPort
     */
    public function setFromPort($fromPort): void
    {
        $this->fromPort = $fromPort;
    }

    /**
     * @param null $toPort
     */
    public function setToPort($toPort): void
    {
        $this->toPort = $toPort;
    }

    /**
     * Returns the edge definition as is requested by GraphViz.
     *
     * @return string
     */
    public function __toString()
    {
        $attributes = array();
        foreach ($this->attributes as $value) {
            $attributes[] = (string)$value;
        }
        $attributes = implode("\n", $attributes);

        $from_name = addslashes($this->getFrom()->getName());
        $to_name = addslashes($this->getTo()->getName());
        $from_name .= (!empty($this->fromPort)) ? ':' . $this->fromPort : '';
        $to_name .= (!empty($this->toPort)) ? ':' . $this->toPort : '';

        return <<<DOT
$from_name -> $to_name [
$attributes
]
DOT;
    }
}