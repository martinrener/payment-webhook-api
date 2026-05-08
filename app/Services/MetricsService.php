<?php

namespace App\Services;

use App\Contracts\MetricsRepositoryInterface;

class MetricsService
{
    public function __construct(
        private MetricsRepositoryInterface $metricsRepo,
    ){}

    public function getMetrics(): array
    {
        return [
            'payments_by_event' => $this->metricsRepo->getPaymentsByEvent(),
            'unique_users_count' => $this->metricsRepo->getUniqueUsersCount(),
            'payments_by_currency' => $this->metricsRepo->getPaymentsByCurrency(),
            'volume_by_day' => $this->metricsRepo->getVolumeByDay(),
        ];
    }
}