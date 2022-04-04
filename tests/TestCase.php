<?php

namespace Dicibi\EloquentModification\Tests;

use Dicibi\EloquentModification\EloquentModificationServiceProvider;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;

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
    }
}
