<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure the environment is set to testing
        putenv('APP_ENV=testing');

        // Set the database connection to sqlite for testing
//        config(['database.default' => 'sqlite']);
    }
}
