<?php

namespace BeyondCode\ErdGenerator\Tests;

use BeyondCode\ErdGenerator\Model;

class ModelTest extends TestCase
{
    /** @test */
    public function it_generates_a_node_name_without_hyphens()
    {
        $model = new Model('Test_Class', 'Test_Class', collect());

        $this->assertEquals('testclass', $model->getNodeName());
    }
}
