<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use App\Jobs\ProcessWebhookPayment;

class WebhookEndpointTest extends TestCase
{
    use RefreshDatabase;
    public function test_webhook_endpoint(): void
    {
        $payload = [
            'event_id' => 'evt_test',
            'payment_id' => 'pay_test',
            'user_id' => 'user_test',
            'amount' => 1000,
            'currency' => 'USD',
            'event' => 'payment.completed',
            'timestamp' => '2026-05-07 10:00:00',
        ];

        Queue::fake();

        $response = $this->postJson('/api/webhooks/payment', $payload);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'ok']);
        Queue::assertPushed(ProcessWebhookPayment::class, 1);
        $this->assertDatabaseCount('event_logs', 0);       
    }
}
