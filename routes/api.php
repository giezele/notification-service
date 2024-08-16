<?php

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Notifications\CustomerNotification;
use App\Http\Controllers\NotificationController;

//Route::post('/send-notification', [NotificationController::class, 'send'])->middleware('throttle:notifications');

Route::middleware('api')->group(function () {
    Route::post('/send-notification', [NotificationController::class, 'send'])
        ->middleware('throttle:notifications');
});

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
