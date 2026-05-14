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
    public function test_duplicate_event_is_ignored(): void
    {
        // Procesamos el mismo evento dos veces directamente en el servicio
        $payload = [
            'event_id'   => 'evt_test',
            'payment_id' => 'pay_test',
            'event'      => 'payment.completed',
            'amount'     => 25000,
            'currency'   => 'USD',
            'user_id'    => 'user_1',
            'timestamp'  => '2026-04-29 12:00:00',
        ];

        $service = app(\App\Services\WebhookService::class);
        $dto = new \App\DTOs\EventLogDto(
            eventId: 'evt_test',
            paymentId: 'pay_test',
            event: 'payment.completed',
            currency: 'USD',
            amount: 25000,
            timestamp: '2026-04-29 12:00:00',
            userId: 'user_1',
            receivedAt: now()->toDateTimeString(),
        );
        
        $service->receivePayment($dto);
        $service->receivePayment($dto); // duplicado

        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('event_logs', 1);
    }
}
