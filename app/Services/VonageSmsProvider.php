<?php

namespace App\Services;

use App\Enums\ChannelType;
use App\Models\User;
use App\Traits\LogsNotifications;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Psr\Http\Client\ClientExceptionInterface;
use Vonage\Client as VonageClient;
use Vonage\SMS\Message\SMS;

class VonageSmsProvider implements NotificationChannel
{
    use LogsNotifications;

    public function __construct(public VonageClient $client)
    {
    }

    /**
     * @param string $to
     * @param string $message
     * @return bool
     * @throws ClientExceptionInterface
     */
    public function send(string $to, string $message): bool
    {
        logger("VonageSmsProvider: Attempting to send SMS to {$to}");

        try {
            $response = $this->client->sms()->send(
                new SMS($to, env('VONAGE_SMS_FROM'), $message)
            );

            $messageStatus = $response->current();
            logger("VonageSmsProvider: SMS status - " . $messageStatus->getStatus());

            if ($messageStatus->getStatus() !== 0) {
                Log::info("VonageSmsProvider: Detected SMS failure");
                Log::error("VonageSmsProvider: SMS failed with status: " . $messageStatus->getStatus());

                return false;
            }

            Log::info("VonageSmsProvider: SMS sent successfully to {$to}");

            $userId = $this->getUserIdFromPhoneNumber($to);
            $this->logNotification(
                userId: $userId,
                message: $message,
                channel: ChannelType::SMS->value,
                phoneNumber: $to
            );

            return true;
        } catch (Exception $e) {
            Log::error("VonageSmsProvider: Error sending SMS - " . $e->getMessage());

            return false;
        }
    }

    private function getUserIdFromPhoneNumber(string $phoneNumber): ?int
    {
        return User::where('phone_number', $phoneNumber)->value('id');
    }
}


