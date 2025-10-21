<?php

declare(strict_types=1);

namespace Ahalpara\ActivityLogEnricher\Tests;

use Ahalpara\ActivityLogEnricher\ActivityLogEnricherServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    protected function getPackageProviders($app): array
    {
        return [
            ActivityLogEnricherServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Set up test database
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Set up activity log configuration
        $app['config']->set('activitylog.activity_model', \Spatie\Activitylog\Models\Activity::class);
        $app['config']->set('activitylog.table_name', 'activity_log');
    }
}
