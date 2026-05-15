<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Payment;
use App\Repositories\EloquentMetricsRepository;

class MetricsRepositoryTest extends TestCase
{
    use RefreshDatabase;
    private EloquentMetricsRepository $metricsRepository;
    protected function setUp(): void
    {
        parent::setUp();
        
        Payment::factory()->create(['event' => 'payment.created', 'currency' => 'USD', 'user_id' => 'user_1']);
        Payment::factory()->create(['event' => 'payment.created', 'currency' => 'USD', 'user_id' => 'user_1']);
        Payment::factory()->create(['event' => 'payment.updated', 'currency' => 'EUR', 'user_id' => 'user_2']);
        $this->metricsRepository = new EloquentMetricsRepository();
    }

    public function test_getUniqueUsers_givesCorrect_count()
    {
        $uniqueUsers = $this->metricsRepository->getUniqueUsersCount();
        $this->assertEquals(2, $uniqueUsers);
    }

    public function test_getsPaymentsByEvent_givesCorrect_counts()
    {
        $paymentsByEvent = $this->metricsRepository->getPaymentsByEvent();
        $this->assertCount(2, $paymentsByEvent);
    }

    public function test_getsPaymentsByCurrency_givesCorrect_counts()
    {
        $paymentsByCurrency = $this->metricsRepository->getPaymentsByCurrency();
        $this->assertCount(2, $paymentsByCurrency);
    }

    public function test_getVolumeByDay_givesCorrect_counts()
    {
        $volumeByDay = $this->metricsRepository->getVolumeByDay();
        $this->assertCount(2, $volumeByDay);
    }
}
