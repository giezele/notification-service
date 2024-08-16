<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function __construct(
        private array $primaryProviders,
        private array $failoverProviders = []
    ) {
    }

    public function send(string $to, string $message): bool
    {
        // Try primary providers first
        foreach ($this->primaryProviders as $provider) {
            if ($provider->send($to, $message)) {
                return true;
            }
        }

        // If all primary providers fail, try failover providers
        Log::info('Primary providers failed, attempting failover providers');
        foreach ($this->failoverProviders as $provider) {
            Log::info('Attempting failover provider: ' . get_class($provider));

            if ($provider->send($to, $message)) {
                Log::debug('Failover provider succeeded: ' . get_class($provider));

                return true;
            }
        }

        Log::error("All providers failed to send the message to {$to}");

        return false;
    }
}

