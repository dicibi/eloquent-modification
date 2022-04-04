<?php

namespace Dicibi\EloquentModification;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class EloquentModificationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/eloquent-modification.php' => base_path('eloquent-modification.php'),
            ]);

            $this->loadMigrationsFrom(__DIR__.'/../migrations');
        }

    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/eloquent-modification.php', 'eloquent-modification');

        $this->app->singleton('eloquent-modification', static function (Application $app) {
            return new EloquentModification(static fn () => [
                $app->get('config'),
            ]);
        });
    }
}
