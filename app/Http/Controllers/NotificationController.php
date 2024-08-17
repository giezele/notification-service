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
use RuntimeException;

class NotificationController extends Controller
{
    use LogsNotifications;

    public function __construct(
        private NotificationService $notificationService,
    ) {
    }

    public function send(SendNotificationRequest $request): JsonResponse
    {
        //todo create a job for sending notification to handle delays, retries
        try {
            $user = $this->findUser($request->input('user_id'));
            $message = $request->input('message');

            if ($user->is_sms_preferred) {
                $this->sendSmsNotification($user->phone_number, $message);
            }

            $this->sendEmailNotification($user, $message);

            return response()->json(['status' => 'Notification sent successfully']);
        } catch (ModelNotFoundException $e) {
            logger('User not found: ' . $e->getMessage());
            return response()->json(['status' => 'Failed to find user', 'error' => $e->getMessage()], 404);
        } catch (RuntimeException $e) {
            logger('Notification error: ' . $e->getMessage());
            return response()->json(['status' => 'Failed to send notification', 'error' => $e->getMessage()], 500);
        }
    }

    private function findUser(string $userId): User
    {
        return User::findOrFail($userId);
    }

    private function sendSmsNotification(string $phoneNumber, string $message): void
    {
        $this->notificationService->send($phoneNumber, $message);
        logger('SMS notification sent to ' . $phoneNumber);
    }

    private function sendEmailNotification(User $user, string $message): void
    {
        try {
            logger('Controller: Attempting to send email');
            $user->notify(new CustomerNotification($user->id, $message));

            $this->logNotification(
                userId: $user->id,
                message: $message,
                channel: ChannelType::MAIL->value,
            );
        } catch (Exception $e) {
            throw new RuntimeException('Failed to send email: ' . $e->getMessage(), 0, $e);
        }
    }
}


