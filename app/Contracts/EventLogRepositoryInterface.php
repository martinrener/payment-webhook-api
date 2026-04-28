<?php

namespace App\Contracts;

interface EventLogRepositoryInterface{
    public function store(EventDto $event): void;
    public function findByPaymentId(string $paymentId): array;
    public function existsEvent(string $eventId): bool;
}