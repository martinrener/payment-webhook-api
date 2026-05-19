<?php

namespace App\Repositories;

use App\Models\Payment;
use App\DTOs\PaymentDto;
use App\Contracts\PaymentRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

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

    public function findByPaymentId(string $paymentId): ?Payment
    {
        return Payment::where('payment_id', $paymentId)->first();
    }

    private function buildFilteredQuery(?string $event, ?string $user_id, ?string $currency, ?string $dateFrom, 
                                        ?string $dateTo): Builder
    {
        return Payment::query()->when($event, function ($query) use ($event) {
                $query->where('event', $event);
            })
            ->when($user_id, function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })
            ->when($currency, function ($query) use ($currency) {
                $query->where('currency', $currency);
            })
            ->when($dateFrom, function ($query) use ($dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($dateTo, function ($query) use ($dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            });
    }

    public function list(int $page = 1,int $perPage = 10, ?string $event = null, ?string $user_id = null, 
                        ?string $currency = null, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        return $this->buildFilteredQuery($event, $user_id, $currency, $dateFrom, $dateTo)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page)
            ->toArray();
    }

    public function export(?string $event = null, ?string $user_id = null, ?string $currency = null, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        return $this->buildFilteredQuery( $event, $user_id, $currency, $dateFrom, $dateTo)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }
}