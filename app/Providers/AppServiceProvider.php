<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use App\Services\NotificationService;
use App\Services\TwilioSmsProvider;
use App\Services\VonageSmsProvider;
use Vonage\Client\Credentials\Basic;
use Vonage\Client as VonageClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(NotificationService::class, function ($app) {
            $basic = new Basic(env('VONAGE_KEY'), env('VONAGE_SECRET'));
            $vonageClient = new VonageClient($basic);

            $vonageProvider = new VonageSmsProvider($vonageClient);

            // Create the Twilio provider
            $twilioProvider = new TwilioSmsProvider();

            // Return the NotificationService with providers
            return new NotificationService([$twilioProvider], [$vonageProvider]);
        });
    }


    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        RateLimiter::for('notifications', function (Request $request) {
            return Limit::perMinute(300)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
