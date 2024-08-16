<?php

namespace App\Services;

use App\Enums\ChannelType;
use App\Models\User;
use App\Traits\LogsNotifications;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Twilio\Exceptions\ConfigurationException;
use Twilio\Rest\Client;

class TwilioSmsProvider implements NotificationChannel
{
    use LogsNotifications;

    public Client $client;

    /**
     * @throws ConfigurationException
     */
    public function __construct()
    {
        $this->client = new Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));
    }

    public function send(string $to, string $message): bool
    {
        logger("TwilioSmsProvider: Sending SMS to {$to}");

        try {
            $this->client->messages->create($to, [
                'from' => env('TWILIO_FROM'),
                'body' => $message,
            ]);

            Log::info("TwilioSmsProvider: SMS sent successfully to {$to}");

            $userId = $this->getUserIdFromPhoneNumber($to);
            $this->logNotification(
                userId: $userId,
                message: $message,
                channel: ChannelType::SMS->value,
                phoneNumber: $to
            );

            return true;
        } catch (\Exception $e) {
            logger("TwilioSmsProvider: Failed to send SMS - " . $e->getMessage());

            return false;
        }
    }

    private function getUserIdFromPhoneNumber(string $phoneNumber): ?int
    {
        return User::where('phone_number', $phoneNumber)->value('id');
    }
}


