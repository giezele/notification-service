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

    public function test_email_is_sent_to_user()
    {
        Notification::fake();

        $user = User::factory()->create();
        $message = "This is a test email message.";

        // Send the notification
        $user->notify(new CustomerNotification($user->id, $message));

        // Assert the notification was sent
        Notification::assertSentTo(
            $user,
            CustomerNotification::class,
            function ($notification, $channels) use ($message, $user) {
                return in_array('mail', $channels) && $notification->toMail($user)->introLines[0] === $message;
            }
        );

        // Assert the notification was logged in the database
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'type' => CustomerNotification::class,
            'data->message' => $message,
            'channel' => 'mail',
        ]);
    }

    public function test_email_sending_fails_and_is_logged(): void
    {
        // Create a test user
        $user = User::factory()->create();

        // Fake notifications
        Notification::fake();

        // Force the mailer to throw a TransportException
        Notification::shouldReceive('send')->andThrow(TransportException::class);

        $message = 'This is a test email message.';

        // Expect the TransportException to be thrown
        $this->expectException(TransportException::class);

        // Send a notification
        $user->notify(new CustomerNotification($user->id, $message));

        // Assert that no notification was sent
        Notification::assertNothingSent();

        // Assert that the notification was not logged in the database
        $this->assertDatabaseMissing('notifications', [
            'notifiable_id' => $user->id,
            'type' => CustomerNotification::class,
            'data->message' => $message,
        ]);

        // Log verification logic (if any specific logging is implemented)
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message) {
                return str_contains($message, 'Email sending failed');
            });
    }
}
