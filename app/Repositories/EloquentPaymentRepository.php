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
            ['payment_id' => $payment->paymentId],
            [
                'event'         => $payment->event,
                'amount'        => $payment->amount,
                'currency'      => $payment->currency,
                'user_id'       => $payment->userId,
                'last_event_id' => $payment->lastEventId,
            ]
        );
    }

    public function findByPaymentId(string $paymentId): Payment
    {
        return Payment::where('payment_id', $paymentId)->findOrFail();
    }
    public function list(int $page = 1,int $perPage = 10): array
    {
        return Payment::orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page)
            ->toArray();
    }
}