<?php

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Notifications\CustomerNotification;
use App\Http\Controllers\NotificationController;



Route::put('/user/update-preferences/{id}', function (Request $request, string $id) {
    $request->validate([
        'phone_number' => 'required|string|max:15',
        'is_sms_preferred' => 'required|boolean',
    ]);

    // Simulate finding the user and updating their preferences
    $user = [
        'id' => $id,
        'phone_number' => $request->input('phone_number'),
        'is_sms_preferred' => $request->input('is_sms_preferred'),
        'email' => 'test@example.com', // Example email, hardcoded for testing
    ];

    // Store the user preferences in a session (for testing purposes)
    session(['user_' . $id => $user]);

    return response()->json(['status' => 'User preferences updated successfully', 'user' => $user], 200);
});


Route::post('/send-notification', [NotificationController::class, 'send']); // SITAS VEIKIA


Route::post('/send-email', function (Request $request) {
    $request->validate([
        'to' => 'required|email',
    ]);
logger($request);
    try {
        Mail::raw('This is a test email sent via failover configuration.', function ($message) use ($request) {
            $message->to($request->input('to'))
                ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                ->subject('Test Email via Failover');
        });

        return response()->json(['status' => 'Email sent successfully']);
    } catch (\Exception $e) {
        Log::error('Email sending failed: ' . $e->getMessage());
        return response()->json(['status' => 'Failed to send email', 'error' => $e->getMessage()], 500);
    }
});


//Route::post('/send-notification', function (Request $request, string $userId) {
//    $user = session('user_' . $userId);
//
//    if (!$user) {
//        return response()->json(['error' => 'User not found or preferences not set.'], 404);
//    }
//
//    $message = $request->input('message');
//    $notification = new CustomerNotification($userId, $request->message);
//
//    if ($user['is_sms_preferred']) {
//        // Send SMS
//        $notification->sendTwillioSMS($user);
//    } else {
//        // Send Email
//        $user->notify($notification);
//    }
//
//    return response()->json(['status' => 'Notification sent successfully'], 200);
//});

Route::post('/send-postmark-email', function (Request $request) {
    $request->validate([
        'to' => 'required|email',
    ]);

    Mail::raw('This is a test email sent via Postmark Sandbox.', function ($message) use ($request) {
        $message->to($request->input('to'))
            ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
            ->subject('Test Postmark Sandbox Email');
    });

    return response()->json(['status' => 'Email sent to Postmark Sandbox']);
});


