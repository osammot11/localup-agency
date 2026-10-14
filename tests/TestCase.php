<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        if (! $app->environment('testing') || config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== ':memory:') {
            throw new RuntimeException('Test interrotti: il database dei test deve essere SQLite in memoria (:memory:).');
        }

        return $app;
    }

    protected function setUp(): void
    {
        if (is_file(dirname(__DIR__).'/bootstrap/cache/config.php')) {
            throw new RuntimeException('Test interrotti: la cache di configurazione Laravel può puntare al database reale. Esegui php artisan config:clear prima dei test.');
        }

        parent::setUp();
    }
}
