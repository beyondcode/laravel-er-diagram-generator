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
        $this->app['config']->set('erd-generator.use_db_schema', false);
        $this->app['config']->set('erd-generator.directories', [__DIR__ . '/Models']);

        Artisan::call('generate:erd', [
            '--format' => 'text'
        ]);

        $this->assertMatchesSnapshot(Artisan::output());
    }

    /** @test */
    public function it_generated_graphviz_for_test_models_with_db_columns_and_types()
    {
        $this->app['config']->set('erd-generator.directories', [__DIR__ . '/Models']);

        Artisan::call('generate:erd', [
            '--format' => 'text'
        ]);

        $this->assertMatchesSnapshot(Artisan::output());
    }

    /** @test */
    public function it_generated_graphviz_for_test_models_with_db_columns()
    {
        $this->app['config']->set('erd-generator.use_column_types', false);
        $this->app['config']->set('erd-generator.directories', [__DIR__ . '/Models']);

        Artisan::call('generate:erd', [
            '--format' => 'text'
        ]);

        $this->assertMatchesSnapshot(Artisan::output());
    }

    /** @test */
    public function it_generated_graphviz_in_jpeg_format()
    {
        $this->app['config']->set('erd-generator.directories', [__DIR__ . '/Models']);

        Artisan::call('generate:erd', [
            '--format' => 'jpeg'
        ]);

        $this->assertStringContainsString('Wrote diagram to graph.jpeg', Artisan::output());
    }

    /** @test */
    public function it_generates_text_output_file()
    {
        $this->app['config']->set('erd-generator.directories', [__DIR__ . '/Models']);
        
        $outputFile = __DIR__ . '/output_test.txt';
        
        // Make sure the file doesn't exist before the test
        if (file_exists($outputFile)) {
            unlink($outputFile);
        }
        
        Artisan::call('generate:erd', [
            'filename' => $outputFile,
            '--text-output' => true
        ]);
        
        $this->assertFileExists($outputFile);
        $this->assertStringContainsString('Wrote text diagram to ' . $outputFile, Artisan::output());
        
        // Check if the file contains GraphViz DOT content
        $fileContent = file_get_contents($outputFile);
        $this->assertStringContainsString('digraph', $fileContent);
        
        // Clean up
        if (file_exists($outputFile)) {
            unlink($outputFile);
        }
    }
    
    /** @test */
    public function it_generates_structured_text_output_file()
    {
        $this->app['config']->set('erd-generator.directories', [__DIR__ . '/Models']);
        
        $outputFile = __DIR__ . '/structured_test.txt';
        
        // Make sure the file doesn't exist before the test
        if (file_exists($outputFile)) {
            unlink($outputFile);
        }
        
        Artisan::call('generate:erd', [
            'filename' => $outputFile,
            '--structured' => true
        ]);
        
        $this->assertFileExists($outputFile);
        $this->assertStringContainsString('Wrote structured text diagram to ' . $outputFile, Artisan::output());
        
        // Check if the file contains structured Markdown content
        $fileContent = file_get_contents($outputFile);
        $this->assertStringContainsString('# Entity Relationship Diagram', $fileContent);
        $this->assertStringContainsString('## Entities', $fileContent);
        $this->assertStringContainsString('## Relationships', $fileContent);
        
        // Clean up
        if (file_exists($outputFile)) {
            unlink($outputFile);
        }
    }
    
    /** @test */
    public function it_generates_structured_text_output_with_correct_content()
    {
        $this->app['config']->set('erd-generator.directories', [__DIR__ . '/Models']);
        
        // Get the structured text output directly from the GraphBuilder
        $models = $this->app->make('BeyondCode\ErdGenerator\ModelFinder')
            ->getModelsInDirectory(__DIR__ . '/Models')
            ->transform(function ($model) {
                return new \BeyondCode\ErdGenerator\Model(
                    $model,
                    (new \ReflectionClass($model))->getShortName(),
                    $this->app->make('BeyondCode\ErdGenerator\RelationFinder')->getModelRelations($model)
                );
            });
        
        $structuredOutput = $this->app->make('BeyondCode\ErdGenerator\GraphBuilder')
            ->generateStructuredTextRepresentation($models);
        
        // Assert the structured output matches the snapshot
        $this->assertMatchesSnapshot($structuredOutput);
    }
}
