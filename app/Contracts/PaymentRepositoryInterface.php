<?php

namespace App\Contracts;

use App\DTOs\PaymentDto;
use App\Models\Payment;

interface PaymentRepositoryInterface
{
    public function upsert(PaymentDto $payment): void;
    public function findByPaymentId(string $payment_id): Payment;
    public function list(int $page = 1, int $perPage = 10): array;
}