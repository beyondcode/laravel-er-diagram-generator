<?php

namespace BeyondCode\ErdGenerator\Tests;

use Illuminate\Database\Schema\Blueprint;
use BeyondCode\ErdGenerator\ErdGeneratorServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function getPackageProviders($app)
    {
        return [ErdGeneratorServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');

        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUpDatabase()
    {
        $this->app['db']->connection()->getSchemaBuilder()->create('avatars', function (Blueprint $table) {
            $table->increments('id');
            $table->string('url');
            $table->timestamps();
        });

        $this->app['db']->connection()->getSchemaBuilder()->create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('title');
            $table->text('body');
            $table->timestamps();
        });

        $this->app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('avatar_id');
            $table->string('name');
            $table->string('email');
            $table->timestamps();
        });

        $this->app['db']->connection()->getSchemaBuilder()->create('comments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('post_id');
            $table->string('body');
            $table->morphs('commentable');
        });
    }

}