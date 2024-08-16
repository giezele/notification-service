<?php

namespace App\Services;

interface NotificationChannel
{
    public function send(string $to, string $message): bool;
}

