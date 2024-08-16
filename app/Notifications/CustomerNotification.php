<?php

namespace App\Notifications;

use App\Enums\ChannelType;
use App\Traits\LogsNotifications;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerNotification extends Notification implements ShouldQueue
{
    use Queueable, LogsNotifications;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private string $userId,
        private string $message,
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = [ChannelType::MAIL->value];

        logger($channels);

        return $channels;
    }

    /**
     * @param object $notifiable
     * @return MailMessage
     * @throws Exception
     */
    public function toMail(object $notifiable): MailMessage
    {
        try {
            return (new MailMessage)
                ->line($this->message)
                ->action('Notification Action', url('/'))
                ->line('Thank you for using GieApp!');

        } catch (Exception $e) {
            logger('Failed to construct Mail message: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * @param object $notifiable
     * @return array
     */
    public function toDatabase(object $notifiable): array
    {
        logger('toDatabase method triggered');

        return [
            'message' => $this->message,
            'user_id' => $this->userId,
            'channel' => ChannelType::DATABASE->value,
        ];
    }
}
