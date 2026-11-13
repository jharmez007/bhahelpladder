<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();

        $connection = (string) $app['config']->get('database.default');
        $url = (string) $app['config']->get("database.connections.{$connection}.url");
        $database = $url !== ''
            ? trim((string) parse_url($url, PHP_URL_PATH), '/')
            : (string) $app['config']->get("database.connections.{$connection}.database");

        if ($connection !== 'pgsql' || ! str_ends_with($database, '_test')) {
            throw new \RuntimeException(
                'Automated tests require an isolated PostgreSQL database whose name ends in _test.'
            );
        }

        return $app;
    }
}
