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
            'event_id'    => $event->event_id,
            'payment_id'  => $event->payment_id,
            'event'       => $event->event,
            'amount'      => $event->amount,
            'currency'    => $event->currency,
            'user_id'     => $event->user_id,
            'timestamp'   => $event->timestamp,
            'received_at' => $event->received_at,
        ]);
    }
    
    public function findByPaymentId(string $payment_id): array 
    {
        return EventLog::where('payment_id',$payment_id)
            ->orderBy('timestamp')
            ->get()
            ->toArray();
    }

    public function existsEvent(string $event_id): bool
    {
        return EventLog::where('event_id', $event_id)->exists();
    }
}