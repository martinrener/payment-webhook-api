<?php

namespace App\DTOs;

class EventLogDto {
    public function __construct(
        public readonly string $event_id,
        public readonly string $payment_id,
        public readonly string $event,
        public readonly string $currency,
        public readonly int $amount,
        public readonly string $timestamp,
        public readonly string $user_id,
        public readonly string $received_at,
    ){}
}