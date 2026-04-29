<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use App\DTOs\EventLogDto;
use App\Http\Requests\StoreWebhookRequest;

class WebhookController extends Controller
{
    public function __construct(
        private WebhookService $webhook_service,
    ) {}

    public function store(StoreWebhookRequest $request): JsonResponse
    {
        try{
            $event = $this->createEventLogDto($request);
            $this->webhook_service->receivePayment($event);
            return response()->json(['message' => 'ok'], 200);
        }catch(\Exception $e){
            \Log::error($e->getMessage());
            return response()->json(['message' => 'error'], 500);
        }
    }

    private function createEventLogDto(StoreWebhookRequest $request): EventLogDto
    {
        return new EventLogDto(
            event_id: $request->event_id,
            payment_id: $request->payment_id,
            event: $request->event,
            currency: strtoupper($request->currency),
            amount: $request->amount,
            user_id: $request->user_id,
            timestamp: $request->timestamp,
            received_at: now(),
        );
    }

    public function getPayments(): JsonResponse
    {
        return response()->json($this->webhook_service->getPayments());
    }

    public function getPaymentEvents(string $payment_id): JsonResponse
    {
        return response()->json($this->webhook_service->getPaymentEvents($payment_id));
    }
}
