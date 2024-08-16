<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationThrottlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_throttling_applies_correctly(): void
    {
        $user = User::factory()->create();

        // Send requests up to the rate limit
        for ($i = 0; $i < 300; $i++) {
            $response = $this->postJson('/api/send-notification', [
                'user_id' => $user->id,
                'message' => 'Test message',
            ]);

            $response->assertStatus(200);
        }

        // The next request should be throttled
        $response = $this->postJson('/api/send-notification', [
            'user_id' => $user->id,
            'message' => 'Test message',
        ]);

        $response->assertStatus(429); // Expect throttling response
    }

    public function test_rate_limiter_resets_after_a_minute(): void
    {
        $user = User::factory()->create();

        // Send requests up to the rate limit
        for ($i = 0; $i < 300; $i++) {
            $response = $this->postJson('/api/send-notification', [
                'user_id' => $user->id,
                'message' => 'Test message',
            ]);

            $response->assertStatus(200);
        }

        // Travel 61 seconds to simulate rate limiter reset
        $this->travel(61)->seconds();

        // The next request should be successful again
        $response = $this->postJson('/api/send-notification', [
            'user_id' => $user->id,
            'message' => 'Test message',
        ]);

        $response->assertStatus(200);
    }
}
