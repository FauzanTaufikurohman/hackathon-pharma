<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        $app = parent::createApplication();
        $driver = $app['config']->get('database.default');
        if ($driver !== 'sqlite' && ! str_ends_with((string) $app['config']->get("database.connections.$driver.database"), '_test')) {
            throw new \RuntimeException('Database tes harus berakhiran _test.');
        }

        return $app;
    }
    //
}
