<?php

namespace App\Http\Controllers;

use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreWebhookRequest;
use App\Jobs\ProcessWebhookPayment;

class WebhookController extends Controller
{
    public function __construct(
        private WebhookService $webhookService,
    ) {}

    public function store(StoreWebhookRequest $request): JsonResponse
    {
        $event = $this->webhookService->createEventLogDto($request->validated());
        ProcessWebhookPayment::dispatch($event);
        return response()->json(['message' => 'ok'], 200);
    }
}
