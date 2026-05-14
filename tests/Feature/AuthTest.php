<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class AuthTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    public function test_login_exitoso(): void
    {
        $user = User::factory()->create([
            'email' => 'test@test.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token']);
    }

    public function test_login_usuario_no_existe(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'fake@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
                    ->assertJson(['message' => 'Invalid credentials']);
    }

    public function test_login_wrong_password(): void
    {
        $user = User::factory()->create([
            'email' => 'test@test.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Invalid credentials']);
    }

    public function test_login_rate_limiting(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $response = $this->postJson('/api/login', [
                'email' => 'fake@test.com',
                'password' => 'wrongpassword',
            ]);
        }

        $response->assertStatus(429);
    }

    public function test_user_autenticado(): void
    {
        $user = User::factory()->create([
            'email' => 'test@test.com',
            'password' => bcrypt('password123'),
        ]);

        $login = $this->postJson('/api/login', [
            'email' => 'test@test.com',
            'password' => 'password123',
        ]);

        $token = $login->json('token');

        $this->withHeaders(['Authorization' => "Bearer $token"])
            ->getJson('/api/user')
            ->assertStatus(200)
            ->assertJsonStructure(['id', 'email', 'name']);
    }
}
