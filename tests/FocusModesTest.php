<?php

namespace BeyondCode\ErdGenerator\Tests;

use BeyondCode\ErdGenerator\ModelFinder;
use BeyondCode\ErdGenerator\RelationFinder;
use BeyondCode\ErdGenerator\Tests\Models\Comment;
use BeyondCode\ErdGenerator\Tests\Models\Post;
use BeyondCode\ErdGenerator\Tests\Models\User;

class FocusModesTest extends TestCase
{
    public function test_focus()
    {
        //arrange
        $finder = new ModelFinder(app()->make('files'), app()->make(RelationFinder::class));

        //act
        $classNames = $finder->setFocus([
            'BeyondCode\ErdGenerator\Tests\Models\Comment',
            ])->getModelsInDirectory(__DIR__ . "/Models");

        //assert
        $this->assertCount(3, $classNames);

        $this->assertSame(
            [Comment::class, Post::class, User::class],
            $classNames->values()->all()
        );
    }
}