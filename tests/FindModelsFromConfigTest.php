<?php

namespace BeyondCode\ErdGenerator\Tests;

use BeyondCode\ErdGenerator\ModelFinder;
use BeyondCode\ErdGenerator\RelationFinder;
use BeyondCode\ErdGenerator\Tests\Models\Avatar;
use BeyondCode\ErdGenerator\Tests\Models\Comment;
use BeyondCode\ErdGenerator\Tests\Models\Post;
use BeyondCode\ErdGenerator\Tests\Models\User;

class FindModelsFromConfigTest extends TestCase
{

    /** @test */
    public function it_can_find_class_names_from_directory()
    {
        $finder = new ModelFinder(app()->make('files'), app()->make(RelationFinder::class));

        $classNames = $finder->getModelsInDirectory(__DIR__ . "/Models");

        $this->assertCount(4, $classNames);

        $this->assertSame(
            [Avatar::class, Comment::class, Post::class, User::class],
            $classNames->values()->all()
        );
    }

    /** @test */
    public function it_will_ignore_a_model_if_it_is_excluded_on_config()
    {
        $this->app['config']->set('erd-generator.ignore', [
            Avatar::class,
            User::class => [
                'posts'
            ]
        ]);

        $finder = new ModelFinder(app()->make('files'), app()->make(RelationFinder::class));

        $classNames = $finder->getModelsInDirectory(__DIR__ . "/Models");

        $this->assertCount(3, $classNames);
        $this->assertEquals(
            [Comment::class, Post::class, User::class],
            $classNames->values()->all()
        );
    }
}
