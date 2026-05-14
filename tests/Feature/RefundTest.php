<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Payment;

class RefundTest extends TestCase
{
    use RefreshDatabase;

    // Route::post('/admin/refund', [AdminController::class, 'refundPayment']);
    public function test_postRefund_withAuthAndAdmin(): void
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
            ->postJson('/api/admin/refund', [
                'payment_id' => $payment->payment_id
            ])
            ->assertStatus(200);
    }

    public function test_postRefund_withAuthButNotAdmin(): void
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
            ->postJson('/api/admin/refund', [
                'payment_id' => $payment->payment_id
            ])
            ->assertStatus(403);
    }

    public function test_postRefund_withAuthAndAdmin_nonExistent_Payment(): void
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

        $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson('/api/admin/refund', [
                'payment_id' => 'pay_fake'
            ])
            ->assertStatus(500);
    }

    public function test_postRefund_sinAuth(): void
    {
        $this->postJson('/api/admin/refund', [
            'payment_id' => 'pay_real'])
             ->assertStatus(401);
    }
}
