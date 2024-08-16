<?php

namespace App\Http\Controllers;

use App\Enums\ChannelType;
use App\Http\Requests\SendNotificationRequest;
use App\Models\User;
use App\Notifications\CustomerNotification;
use App\Services\NotificationService;
use App\Traits\LogsNotifications;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    use LogsNotifications;

    public function __construct(
        private NotificationService $notificationService,
    ) {
    }

    public function send(SendNotificationRequest $request): JsonResponse
    {
        try {
            $user = User::findOrFail($request->input('user_id'));
            $message = $request->input('message');

            if ($user->is_sms_preferred) {
                $this->notificationService->send($user->phone_number, $message);
            }

            try {
                logger('Controller: Attempting to send email');
                $user->notify(new CustomerNotification($user->id, $message));

                $this->logNotification(
                    userId: $user->id,
                    message: $message,
                    channel: ChannelType::MAIL->value,
                );
            } catch (Exception $e) {
                logger('Email sending failed: ' . $e->getMessage());

                return response()->json(['status' => 'Failed to send email', 'error' => $e->getMessage()], 500);
            }
        } catch (Exception $e) {
            logger('Exception: ' . $e->getMessage());

            return response()->json(['status' => 'Failed to send notification', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['status' => 'Notification sent successfully']);
    }



    public function send1(SendNotificationRequest $request): JsonResponse //optimized
    {
        try {
            $user = User::findOrFail($request->input('user_id'));
            $message = $request->input('message');

            // Send SMS if preferred
            if ($user->is_sms_preferred) {
                $this->notificationService->send($user->phone_number, $message);
                $this->logNotification(
                    userId: $user->id,
                    message: $message,
                    channel: ChannelType::SMS->value,
                    phoneNumber: $user->phone_number
                );
            }

            // Attempt to send email
            logger('Controller: Attempting to send email');
            $user->notify(new CustomerNotification($user->id, $message));

            // Log email notification
            $this->logNotification(
                userId: $user->id,
                message: $message,
                channel: ChannelType::MAIL->value
            );
        } catch (Exception $e) {
            logger('Notification sending failed: ' . $e->getMessage());

            return response()->json(['status' => 'Failed to send notification', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['status' => 'Notification sent successfully']);
    }
}


