<?php

namespace App\DTOs;

class PaymentDto {
    public function __construct(
        public readonly string $payment_id,
        public readonly string $event,
        public readonly string $currency,
        public readonly int $amount,
        public readonly string $user_id,
        public readonly string $last_event_id,
    ){}
}