<?php

namespace Dicibi\EloquentModification\Tests;

use Dicibi\EloquentModification\EloquentModificationServiceProvider;
use Illuminate\Foundation\Auth\User;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    use WithWorkbench;

    protected function getPackageProviders($app): array
    {
        return [
            EloquentModificationServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('eloquent-modification.models.modifier', [
            'submitter' => User::class,
            'applier' => User::class,
            'reviewer' => User::class,
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->loadMigrationsFrom(dirname(__DIR__) . '/migrations');
    }
}
