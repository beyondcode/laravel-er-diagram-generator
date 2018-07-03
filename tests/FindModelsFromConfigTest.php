<?php

namespace BeyondCode\ErdGenerator\Tests;

use BeyondCode\ErdGenerator\GraphBuilder;
use BeyondCode\ErdGenerator\ModelFinder;
use BeyondCode\ErdGenerator\Tests\Models\Avatar;
use BeyondCode\ErdGenerator\GenerateDiagramCommand;

class FindModelsFromConfigTest extends TestCase
{

    /** @test */
    public function it_can_find_class_names_from_directory()
    {
        $finder = new ModelFinder(app()->make('files'));

        $classNames = $finder->getModelsInDirectory("./tests/Models");

        $this->assertCount(4, $classNames);
        $this->assertSame(Avatar::class, $classNames->first());
    }
}