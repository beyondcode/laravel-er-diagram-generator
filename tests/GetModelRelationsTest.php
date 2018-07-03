<?php

namespace BeyondCode\ErdGenerator\Tests;

use BeyondCode\ErdGenerator\GraphBuilder;
use BeyondCode\ErdGenerator\ModelRelation;
use BeyondCode\ErdGenerator\Tests\Models\Post;
use BeyondCode\ErdGenerator\Tests\Models\User;
use BeyondCode\ErdGenerator\Tests\Models\Avatar;
use BeyondCode\ErdGenerator\GenerateDiagramCommand;

class GetModelRelationsTest extends TestCase
{

    /** @test */
    public function it_can_find_model_relations()
    {
        $method = self::getMethod(GenerateDiagramCommand::class, 'getModelRelations');

        $cmd = new GenerateDiagramCommand(app()->make('files'), new GraphBuilder());

        $relations = $method->invokeArgs($cmd, [User::class]);

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
    }

    protected static function getMethod($class, $name)
    {
        $class = new \ReflectionClass($class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
}