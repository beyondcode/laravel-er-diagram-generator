<?php

namespace BeyondCode\ErdGenerator;

use Illuminate\Support\ServiceProvider;

class ErdGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => base_path('config/erd-generator.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'erd-generator');

        $this->app->bind('command.generate:diagram', GenerateDiagramCommand::class);

        $this->commands([
            'command.generate:diagram',
        ]);
    }
}
