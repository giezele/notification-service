<?php

namespace Tests\Feature;

use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_notification_with_primary_provider(): void
    {
        // Mock the primary provider
        $primaryProvider = Mockery::mock(\App\Services\TwilioSmsProvider::class);
        $primaryProvider->shouldReceive('send')->andReturnTrue();

        // Mock the failover provider
        $failoverProvider = Mockery::mock(\App\Services\VonageSmsProvider::class);
        $failoverProvider->shouldNotReceive('send');

        $service = new NotificationService([$primaryProvider], [$failoverProvider]);

        $result = $service->send('1234567890', 'Test message');

        $this->assertTrue($result);
    }

    public function test_send_notification_with_failover_provider(): void
    {
        // Mock the primary provider to fail
        $primaryProvider = Mockery::mock(\App\Services\TwilioSmsProvider::class);
        $primaryProvider->shouldReceive('send')->andReturnFalse();

        // Mock the failover provider to succeed
        $failoverProvider = Mockery::mock(\App\Services\VonageSmsProvider::class);
        $failoverProvider->shouldReceive('send')->andReturnTrue();

        $service = new NotificationService([$primaryProvider], [$failoverProvider]);

        $result = $service->send('1234567890', 'Test message');

        // Assert the notification was sent successfully via the failover provider
        $this->assertTrue($result);
    }
}

