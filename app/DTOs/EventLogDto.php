<?php

namespace App\DTOs;

class EventLogDto {
    public function __construct(
        public readonly string $eventId,
        public readonly string $paymentId,
        public readonly string $event,
        public readonly string $currency,
        public readonly int $amount,
        public readonly string $timestamp,
        public readonly string $userId,
        public readonly string $receivedAt,
    ){}
}