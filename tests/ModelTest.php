<?php

namespace BeyondCode\ErdGenerator\Tests;

use BeyondCode\ErdGenerator\Model;
use PHPUnit\Framework\Attributes\Test;

class ModelTest extends TestCase
{
    #[Test]
    public function it_generates_a_node_name_without_hyphens()
    {
        $model = new Model('Test_Class', 'Test_Class', collect());

        $this->assertEquals('testclass', $model->getNodeName());
    }
}
