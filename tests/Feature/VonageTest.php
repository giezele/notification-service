<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Services\VonageSmsProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;
use Vonage\Client as VonageClient;
use Vonage\SMS\Client as SmsClient;
use Vonage\SMS\Collection;
use Vonage\SMS\Message\SMS;
use Vonage\SMS\SentSMS;

class VonageTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();

        // Force environment to testing
        putenv('APP_ENV=testing');
        $_ENV['APP_ENV'] = 'testing';
        $_SERVER['APP_ENV'] = 'testing';
    }

    public function test_send_sms_successfully(): void
    {
        // Mock the Vonage SMS Client and the response
        $mockSmsClient = Mockery::mock(SmsClient::class);
        $mockCollection = Mockery::mock(Collection::class);
        $mockSentSMS = Mockery::mock(SentSMS::class);

        $mockSentSMS->shouldReceive('getStatus')->andReturn(0);
        $mockCollection->shouldReceive('current')->andReturn($mockSentSMS);
        $mockSmsClient->shouldReceive('send')->andReturn($mockCollection);

        // Mock the Vonage Client to return the mocked SMS client
        $mockClient = Mockery::mock(VonageClient::class);
        $mockClient->shouldReceive('sms')->andReturn($mockSmsClient);

        // Inject the mock client into the VonageSmsProvider
        $provider = new VonageSmsProvider($mockClient);

        // Create a test user
        $user = User::factory()->create(['phone_number' => '1234567890']);
        Log::info("Test User ID: {$user->id}");

        // Execute the send method
        $result = $provider->send($user->phone_number, 'Test message');

        // Assert the SMS was sent successfully
        $this->assertTrue($result);

        // Assert that a notification was logged in the database
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'channel' => 'sms',
            'data->message' => 'Test message',
        ]);
    }

    public function test_send_sms_fails(): void
    {
        // Mock the Vonage SMS Client to simulate a failure
        $mockSmsClient = Mockery::mock(SmsClient::class);
        $mockCollection = Mockery::mock(Collection::class);
        $mockSentSMS = Mockery::mock(SentSMS::class);

        $mockSentSMS->shouldReceive('getStatus')->andReturn(1); // Non-zero indicates failure
        $mockCollection->shouldReceive('current')->andReturn($mockSentSMS);
        $mockSmsClient->shouldReceive('send')->andReturn($mockCollection);

        // Mock the Vonage Client to return the mocked SMS client
        $mockClient = Mockery::mock(VonageClient::class);
        $mockClient->shouldReceive('sms')->andReturn($mockSmsClient);

        // Inject the mock client into the VonageSmsProvider
        $provider = new VonageSmsProvider($mockClient);

        // Create a test user
        $user = User::factory()->create(['phone_number' => '1234567890']);

        // Execute the send method
        $result = $provider->send($user->phone_number, 'Test message');

        // Assert the SMS was not sent successfully
        $this->assertFalse($result);

        // Assert that no notification was logged in the database
        $this->assertDatabaseMissing('notifications', [
            'notifiable_id' => $user->id,
            'channel' => 'sms',
            'data->message' => 'Test message',
        ]);
    }


    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
