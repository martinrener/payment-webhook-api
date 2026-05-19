<?php

namespace App\Http\Controllers\Payment;

use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;

class GetPaymentEventsController extends Controller
{
    public function __construct(
        private WebhookService $webhookService,
    ) {}

    public function __invoke(string $paymentId): JsonResponse
    {
        Gate::authorize('access-admin');
        return response()->json($this->webhookService->getPaymentEvents($paymentId));
    }
}
