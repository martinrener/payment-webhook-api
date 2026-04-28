<?php

namespace App\Contracts;

interface PaymentRepositoryInterface
{
    public function upsert(PaymentDto $payment): void;
    public function findByPaymentId(string $payment_id): Payment;
    public function list(): array;
}