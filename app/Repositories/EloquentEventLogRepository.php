<?php

namespace App\Repositories;

use App\Models\EventLog;
use App\DTOs\EventLogDto;
use App\Contracts\EventLogRepositoryInterface;

class EloquentEventLogRepository implements EventLogRepositoryInterface 
{
    public function store(EventLogDto $event): void
    {
        EventLog::create([
            'event_id'    => $event->eventId,
            'payment_id'  => $event->paymentId,
            'event'       => $event->event,
            'amount'      => $event->amount,
            'currency'    => $event->currency,
            'user_id'     => $event->userId,
            'timestamp'   => $event->timestamp,
            'received_at' => $event->receivedAt,
        ]);
    }

    public function findByPaymentId(string $paymentId): array
    {
        return EventLog::where('payment_id', $paymentId)
            ->orderBy('timestamp')
            ->get()
            ->toArray();
    }

    public function existsEvent(string $eventId): bool
    {
        return EventLog::where('event_id', $eventId)->exists();
    }
}