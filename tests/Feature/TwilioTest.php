<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TwilioSmsProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;
use Twilio\Rest\Client;

class TwilioTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_sms_successfully(): void
   {
       // Create a mock for the messages property
       $mockMessages = Mockery::mock();
       $mockMessages->shouldReceive('create')
           ->once()
           ->andReturnTrue();

       // Mock the Twilio Client
       $mockClient = Mockery::mock(Client::class);
       $mockClient->messages = $mockMessages;

       // Swap the real Twilio client with our mock
       $provider = new TwilioSmsProvider();
       $provider->client = $mockClient;

       $user = User::factory()->create(['phone_number' => '1234567890']);

       $result = $provider->send($user->phone_number, 'Test message');

       // Assert the SMS was sent successfully
       $this->assertTrue($result);
   }

    public function test_send_sms_fails(): void
    {
        // Mock the Twilio client to throw an exception
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('messages->create')->andThrow(new \Exception('Failed to send SMS'));

        // Swap the real Twilio client with our mock
        $provider = new TwilioSmsProvider();
        $provider->client = $mockClient;

        $user = User::factory()->create(['phone_number' => '1234567890']);

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
