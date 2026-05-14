<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class ExportTest extends TestCase
{
    use RefreshDatabase;
    //  Route::get('/payments/export', [WebhookController::class, 'exportPayments']);
    public function test_getExport_withAuthAndAdmin(): void
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
            ->get('/api/payments/export')
            ->assertStatus(200)
            ->assertHeader('Content-Disposition', 'attachment; filename=payments.csv');
    }

    public function test_getExport_withAuthButNotAdmin(): void
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
            ->getJson('/api/payments/export')
            ->assertStatus(403);
    }

    public function test_getExport_sinAuth(): void
    {
        $this->getJson('/api/payments/export')
            ->assertStatus(401);
    }
}
