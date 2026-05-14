<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Payment;
use App\Models\EventLog;

class EventsTest extends TestCase
{
    use RefreshDatabase;
    public function test_getEvents_withAuthAndAdmin_noEvents(): void
    {
        $user = User::factory()->create([
            'email' => 'test@test.com',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);

        $login = $this->postJson('/api/login', [
            'email' => 'test@test.com',
            'password' => 'password123',
        ]);

        $token = $login->json('token');

        $payment = Payment::factory()->create(['payment_id' => 'pay_real']);

        $this->withHeaders(['Authorization' => "Bearer $token"])
            ->getJson('/api/payments/' . $payment->payment_id . '/events')
            ->assertStatus(200)
            ->assertJson([]);
    }

    public function test_getEvents_withAuthAndAdmin_Event(): void
    {
        $user = User::factory()->create([
            'email' => 'test@test.com',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);

        $login = $this->postJson('/api/login', [
            'email' => 'test@test.com',
            'password' => 'password123',
        ]);

        $token = $login->json('token');

        $payment = Payment::factory()->create(['payment_id' => 'pay_real']);

        $event = EventLog::factory()->create([
            'payment_id' => 'pay_real',
            'event' => 'payment.created',
            'amount' => 500,
            'currency' => 'USD',
            'user_id' => $user->id,
        ]);

        $this->withHeaders(['Authorization' => "Bearer $token"])
            ->getJson('/api/payments/' . $payment->payment_id . '/events')
            ->assertStatus(200)
            ->assertJson([
                [
                    'event_id' => $event->event_id,
                    'payment_id' => $event->payment_id,
                    'event' => $event->event,
                    'amount' => $event->amount,
                    'currency' => $event->currency,
                    'user_id' => $event->user_id,
                    'timestamp' => $event->timestamp,
                    'received_at' => $event->received_at,
                ]
            ]);
    }

    public function test_getEvents_withAuthButNotAdmin(): void
    {
        $user = User::factory()->create([
            'email' => 'test@test.com',
            'password' => bcrypt('password123'),
            'is_admin' => false,
        ]);

        $login = $this->postJson('/api/login', [
            'email' => 'test@test.com',
            'password' => 'password123',
        ]);

        $token = $login->json('token');

        $payment = Payment::factory()->create(['payment_id' => 'pay_real']);

        $this->withHeaders(['Authorization' => "Bearer $token"])
            ->getJson('/api/payments/' . $payment->payment_id . '/events')
            ->assertStatus(403);
    }

    public function test_getEvents_sinAuth(): void
    {
        $this->getJson('/api/payments/pay_123/events')
            ->assertStatus(401);
    }
}
