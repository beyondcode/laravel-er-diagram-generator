<?php

namespace BeyondCode\ErdGenerator\Tests;

use BeyondCode\ErdGenerator\GraphBuilder;
use BeyondCode\ErdGenerator\Tests\Models\Avatar;
use BeyondCode\ErdGenerator\GenerateDiagramCommand;

class FindModelsFromConfigTest extends TestCase
{

    /** @test */
    public function it_can_find_class_names_from_directory()
    {
        $method = self::getMethod(GenerateDiagramCommand::class, 'getModelInstancesInDirectory');

        $cmd = new GenerateDiagramCommand(app()->make('files'), new GraphBuilder());

        $classNames = $method->invokeArgs($cmd, ["./tests/Models"]);

        $this->assertCount(4, $classNames);
        $this->assertSame(Avatar::class, $classNames->first());
    }

    protected static function getMethod($class, $name)
    {
        $class = new \ReflectionClass($class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
}