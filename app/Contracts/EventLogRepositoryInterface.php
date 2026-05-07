<?php

namespace App\Contracts;

use App\DTOs\EventLogDto;

interface EventLogRepositoryInterface
{
    public function store(EventLogDto $event): void;
    public function findByPaymentId(string $paymentId): array;
    public function existsEvent(string $eventId): bool;
}