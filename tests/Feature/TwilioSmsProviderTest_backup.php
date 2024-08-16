<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TwilioSmsProvider;
use Mockery;
use Tests\TestCase;
use Twilio\Rest\Client;

class TwilioSmsProviderTest extends TestCase
{
    public function test_send_sms_successfully(): void
    {
        // Mock the Twilio client
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('messages->create')->andReturnTrue();

        // Swap the real Twilio client with our mock
        $provider = new TwilioSmsProvider();
        $provider->client = $mockClient;

        // Create a test user
        $user = User::factory()->create(['phone_number' => '1234567890']);

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
        // Mock the Twilio client to throw an exception
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('messages->create')->andThrow(new \Exception('Failed to send SMS'));

        // Swap the real Twilio client with our mock
        $provider = new TwilioSmsProvider();
        $provider->client = $mockClient;

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
