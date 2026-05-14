<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class MetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_getMetrics_withAuthButNotAdmin(): void
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
            ->getJson('/api/metrics')
            ->assertStatus(403);
    }
    public function test_getMetrics_withAuthAndAdmin(): void
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
            ->getJson('/api/metrics')
            ->assertStatus(200)
            ->assertJsonStructure([
                'payments_by_event' => [
                    '*' => ['event', 'total']
                ],
                'payments_by_currency' => [
                    '*' => ['currency', 'total']
                ],
                'volume_by_day' => [
                    '*' => ['date', 'currency', 'total']
                ],
                'unique_users_count'
            ]);
    }

    public function test_getMetrics_sinAuth(): void
    {
        $this->getJson('/api/metrics')
            ->assertStatus(401);
    }
}
