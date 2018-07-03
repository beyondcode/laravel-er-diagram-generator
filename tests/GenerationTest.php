<?php

namespace BeyondCode\ErdGenerator\Tests;

use Spatie\Snapshots\MatchesSnapshots;
use Illuminate\Support\Facades\Artisan;

class GenerationTest extends TestCase
{
    use MatchesSnapshots;

    /** @test */
    public function it_generated_graphviz_for_test_models()
    {
        $this->app['config']->set('erd-generator.directories', [__DIR__.'/Models']);

        Artisan::call('generate:erd', [
            '--format' => 'text'
        ]);

        $this->assertMatchesSnapshot(Artisan::output());
    }
}
