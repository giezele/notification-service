<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_environment_is_testing(): void
    {
        echo "Current environment: " . app()->environment();
        echo "\nDB_CONNECTION: " . env('DB_CONNECTION');
        $this->assertEquals('testing', app()->environment());
    }
}
