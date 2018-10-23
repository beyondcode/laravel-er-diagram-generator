<?php

namespace BeyondCode\ErdGenerator\Tests;

use BeyondCode\ErdGenerator\ModelFinder;
use BeyondCode\ErdGenerator\Tests\Models\Comment;
use BeyondCode\ErdGenerator\Tests\Models\Post;
use BeyondCode\ErdGenerator\Tests\Models\User;
use BeyondCode\ErdGenerator\Tests\Models\User_Avatar;

class FindModelsFromConfigTest extends TestCase
{

    /** @test */
    public function it_can_find_class_names_from_directory()
    {
        $finder = new ModelFinder(app()->make('files'));

        $classNames = $finder->getModelsInDirectory(__DIR__ . "/Models");

        $this->assertCount(4, $classNames);

        $this->assertSame(
            [Comment::class, Post::class, User::class, User_Avatar::class],
            $classNames->values()->all()
        );
    }

    /** @test */
    public function it_will_ignore_a_model_if_it_is_excluded_on_config()
    {
        $this->app['config']->set('erd-generator.ignore', [
            User_Avatar::class,
            User::class => [
                'posts'
            ]
        ]);

        $finder = new ModelFinder(app()->make('files'));

        $classNames = $finder->getModelsInDirectory(__DIR__ . "/Models");

        $this->assertCount(3, $classNames);
        $this->assertEquals(
            [Comment::class, Post::class, User::class],
            $classNames->values()->all()
        );
    }
}
