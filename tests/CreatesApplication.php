<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication(): Application
    {
	// Directly set environment to testing
   	 putenv('APP_ENV=testing');
   	 $_ENV['APP_ENV'] = 'testing';
   	 $_SERVER['APP_ENV'] = 'testing';

        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
