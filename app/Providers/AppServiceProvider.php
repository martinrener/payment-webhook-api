<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\EventLogRepositoryInterface;
use App\Contracts\PaymentRepositoryInterface;
use App\Repositories\EloquentEventLogRepository;
use App\Repositories\EloquentPaymentRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            EventLogRepositoryInterface::class,
            EloquentEventLogRepository::class
        );

        $this->app->bind(
            PaymentRepositoryInterface::class,
            EloquentPaymentRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
