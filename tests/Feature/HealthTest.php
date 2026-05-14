<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class HealthTest extends TestCase
{
    /*
    Route::get('/health', function () {
        return response()->json(['status' => 'ok'], 200);
    });
     */
    public function test_getHealth_ok(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
                ->assertJson(['status' => 'ok']);
    }
}
