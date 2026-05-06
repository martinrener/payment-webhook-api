<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use App\DTOs\EventLogDto;
use App\Http\Requests\StoreWebhookRequest;
use App\Jobs\ProcessWebhookPayment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;

class WebhookController extends Controller
{
    public function __construct(
        private WebhookService $webhookService,
    ) {}

    public function store(StoreWebhookRequest $request): JsonResponse
    {
        $event = $this->createEventLogDto($request);
        ProcessWebhookPayment::dispatch($event);
        return response()->json(['message' => 'ok'], 200);
    }


    private function createEventLogDto(StoreWebhookRequest $request): EventLogDto
    {
        return new EventLogDto(
            eventId: $request->event_id,
            paymentId: $request->payment_id,
            event: $request->event,
            currency: strtoupper($request->currency),
            amount: $request->amount,
            userId: $request->user_id,
            timestamp: $request->timestamp,
            receivedAt: now(),
        );
    }

    public function getPayments(Request $request): JsonResponse
    {
        Gate::authorize('access-admin');
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 10);
        $event = $request->query('event');
        $user_id = $request->query('user_id');
        $currency = $request->query('currency');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        return response()->json($this->webhookService->getPayments($page, $perPage, $event, $user_id, $currency, $dateFrom, $dateTo));
    }

    public function getPaymentEvents(string $paymentId): JsonResponse
    {
        Gate::authorize('access-admin');
        return response()->json($this->webhookService->getPaymentEvents($paymentId));
    }
}
