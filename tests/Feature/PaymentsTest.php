<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class PaymentsTest extends TestCase
{    
    use RefreshDatabase;
    public function test_getPayments_withAuthAndAdmin(): void
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
            ->getJson('/api/payments')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'payment_id', 'event', 'amount', 'currency', 'user_id', 'last_event_id', 'created_at', 'updated_at']
                ],
                'current_page',
                'total',
                'per_page',
                'last_page'
            ]);
    }

    public function test_getPayments_withAuthButNotAdmin(): void
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

        $this->withHeaders(['Authorization' => "Bearer $token"])
            ->getJson('/api/payments')
            ->assertStatus(403);
    }

    public function test_getPayments_sinAuth(): void
    {
        $this->getJson('/api/payments')
            ->assertStatus(401);
    }
}
