<?php

namespace BeyondCode\ErdGenerator\Tests;

use BeyondCode\ErdGenerator\ModelRelation;
use BeyondCode\ErdGenerator\RelationFinder;
use BeyondCode\ErdGenerator\Tests\Models\Avatar;
use BeyondCode\ErdGenerator\Tests\Models\Comment;
use BeyondCode\ErdGenerator\Tests\Models\Post;
use BeyondCode\ErdGenerator\Tests\Models\User;

class GetModelRelationsTest extends TestCase
{

    /** @test */
    public function it_can_find_model_relations()
    {
        $finder = new RelationFinder();

        $relations = $finder->getModelRelations(User::class);

        $this->assertCount(3, $relations);

        $posts = $relations['posts'];

        $this->assertInstanceOf(ModelRelation::class, $posts);
        $this->assertSame('HasMany', $posts->getType());
        $this->assertSame('posts', $posts->getName());
        $this->assertSame(Post::class, $posts->getModel());
        $this->assertSame('id', $posts->getLocalKey());
        $this->assertSame('user_id', $posts->getForeignKey());

        $avatar = $relations['avatar'];

        $this->assertInstanceOf(ModelRelation::class, $avatar);
        $this->assertSame('avatar', $avatar->getName());
        $this->assertSame('HasOne', $avatar->getType());
        $this->assertSame(Avatar::class, $avatar->getModel());
        $this->assertSame('id', $avatar->getLocalKey());
        $this->assertSame('user_id', $avatar->getForeignKey());

        $avatar = $relations['comments'];

        $this->assertInstanceOf(ModelRelation::class, $avatar);
        $this->assertSame('comments', $avatar->getName());
        $this->assertSame('BelongsToMany', $avatar->getType());
        $this->assertSame(Comment::class, $avatar->getModel());
        $this->assertSame(null, $avatar->getLocalKey());
        $this->assertSame(null, $avatar->getForeignKey());
    }

    /** @test */
    public function it_will_ignore_a_relation_if_it_is_excluded_on_config()
    {
        $this->app['config']->set('erd-generator.ignore', [
            User::class => [
                'posts'
            ]
        ]);

        $finder = new RelationFinder();

        $relations = $finder->getModelRelations(User::class);

        $this->assertCount(2, $relations);
        $this->assertNull(array_get($relations, 'posts'));
    }

}