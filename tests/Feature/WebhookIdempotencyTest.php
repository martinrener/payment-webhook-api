<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;


class WebhookIdempotencyTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_duplicate_event_creates_log_but_not_updates_payment(): void
    {
        $this->postJson('/api/webhooks/payment', [
            'event_id'   => 'evt_test',
            'payment_id' => 'pay_test',
            'event'      => 'payment.completed',
            'amount'     => 25000,
            'currency'   => 'USD',
            'user_id'    => 'user_1',
            'timestamp'  => '2026-04-29 12:00:00',
        ]);
        $this->postJson('/api/webhooks/payment', [
            'event_id'   => 'evt_test',
            'payment_id' => 'pay_test',
            'event'      => 'payment.completed',
            'amount'     => 25000,
            'currency'   => 'USD',
            'user_id'    => 'user_1',
            'timestamp'  => '2026-04-29 12:00:00',
        ]);

        $this->assertDatabaseCount('event_logs', 2);
        $this->assertDatabaseCount('payments', 1);
    }
}
