<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;

class WebhookController extends Controller
{
    public function __construct(
        private WebhookService $webhook_service,
    ) {}

    public function store(StoreWebhookRequest $request): JsonResponse
    {
        try{
            $event = $this->createEventLogDto($request);
        }catch(\Exception $e){

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
}
