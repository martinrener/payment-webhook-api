<?php

namespace App\DTOs;

class PaymentDto {
    public function __construct(
        public readonly string $paymentId,
        public readonly string $event,
        public readonly string $currency,
        public readonly int $amount,
        public readonly string $userId,
        public readonly string $lastEventId,
    ){}
}