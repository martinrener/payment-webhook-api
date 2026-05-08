<?php

namespace App\Repositories;

use App\Contracts\MetricsRepositoryInterface;
use Illuminate\Support\Facades\DB;
use App\Models\Payment;

class EloquentMetricsRepository implements MetricsRepositoryInterface 
{
    public function getPaymentsByEvent(): array{
        return Payment::select('event', DB::raw('count(*) as total'))
            ->groupBy('event')
            ->get()
            ->toArray();
    }
    public function getUniqueUsersCount(): int {
        return Payment::distinct('user_id')->count('user_id');
    }
    public function getPaymentsByCurrency(): array{
        return Payment::select('currency', DB::raw('count(*) as total'))
            ->groupBy('currency')
            ->get()
            ->toArray();
    }
    public function getVolumeByDay(): array{
        return Payment::select(DB::raw('DATE(created_at) as date'), 'currency',DB::raw('sum(amount) as total'))
            ->groupBy(DB::raw('DATE(created_at)'),'currency')
            ->get()
            ->toArray();
    }
}