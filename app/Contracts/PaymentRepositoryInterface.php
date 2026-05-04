<?php

namespace App\Contracts;

use App\DTOs\PaymentDto;
use App\Models\Payment;

interface PaymentRepositoryInterface
{
    public function upsert(PaymentDto $payment): void;
    public function findByPaymentId(string $paymentId): Payment;
    public function list(int $page = 1,int $perPage = 10, string $event = null, string $user_id = null, string $currency = null, string $dateFrom = null, string $dateTo = null): array;
}