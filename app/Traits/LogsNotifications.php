<?php

namespace App\Traits;

use App\Enums\ChannelType;
use App\Models\User;
use App\Notifications\CustomerNotification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait LogsNotifications
{
    public function logNotification(string $userId, string $message, string $channel, ?string $type = null, ?string $phoneNumber = null): void
    {
        $data = ['message' => $message];

        if ($phoneNumber) {
            $data['phone_number'] = $phoneNumber;
        }

        $notificationType = $type ?? ($channel === ChannelType::SMS->value ? NotificationService::class : CustomerNotification::class);

        DB::table('notifications')->insert([
            'id' => (string) Str::uuid(),
            'type' => $notificationType,
            'notifiable_id' => $userId,
            'notifiable_type' => User::class,
            'data' => json_encode($data),
            'channel' => $channel,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

