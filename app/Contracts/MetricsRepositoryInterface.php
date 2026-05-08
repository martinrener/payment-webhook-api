<?php

namespace App\Contracts;

interface MetricsRepositoryInterface
{
    public function getPaymentsByEvent(): array;
    public function getUniqueUsersCount(): int;
    public function getPaymentsByCurrency(): array;
    public function getVolumeByDay(): array;
}