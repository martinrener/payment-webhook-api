<?php

namespace App\Repositories;

use App\Models\Payment;
use App\DTOs\PaymentDto;
use App\Contracts\PaymentRepositoryInterface;

class EloquentPaymentRepository implements PaymentRepositoryInterface 
{
    public function upsert(PaymentDto $payment): void
    {
        Payment::updateOrCreate(
            ['payment_id' => $payment->payment_id],
            [
                'event'       => $payment->event,
                'amount'      => $payment->amount,
                'currency'    => $payment->currency,
                'user_id'     => $payment->user_id,
                'last_event_id' => $payment->last_event_id
            
            ]
        );
    }
    public function findByPaymentId(string $payment_id): Payment 
    {
        return Payment::where('payment_id', $payment_id)->FindOrFail();
    }
    public function list(): array
    {
        return Payment::orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }
}