<?php

namespace App\Contracts;

interface EventLogRepositoryInterface
{
    public function store(EventLogDto $event): void;
    public function findByPaymentId(string $payment_id): array;
    public function existsEvent(string $event_id): bool;
}