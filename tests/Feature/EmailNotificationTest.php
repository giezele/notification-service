<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\CustomerNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Symfony\Component\Mailer\Exception\TransportException;

class EmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_is_sent_to_user(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $message = "This is a test email message.";

        $response = $this->postJson('/api/send-notification', [
            'user_id' => $user->id,
            'message' => $message,
        ]);

        $response->assertStatus(200);

        // Assert the notification was sent via the mail channel
        Notification::assertSentTo(
            $user,
            CustomerNotification::class,
            function (CustomerNotification $notification, $channels) use ($message, $user) {
                // Check that the mail channel is present
                return in_array('mail', $channels) && $notification->toMail($user)->introLines[0] === $message;
            }
        );

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'type' => CustomerNotification::class,
            'data->message' => $message,
            'channel' => 'mail',
        ]);
    }

    public function test_email_sending_fails_and_is_logged(): void
    {
        $user = User::factory()->create();

        Notification::fake();

        Notification::shouldReceive('send')->andThrow(TransportException::class);

        $message = 'This is a test email message.';

        $this->expectException(TransportException::class);

        $user->notify(new CustomerNotification($user->id, $message));

        Notification::assertNothingSent();

        $this->assertDatabaseMissing('notifications', [
            'notifiable_id' => $user->id,
            'type' => CustomerNotification::class,
            'data->message' => $message,
        ]);
    }
}
