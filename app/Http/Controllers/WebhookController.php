<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use App\DTOs\EventLogDto;
use App\Http\Requests\StoreWebhookRequest;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        private WebhookService $webhookService,
    ) {}

    public function store(StoreWebhookRequest $request): JsonResponse
    {
        Log::info('Webhook received', [
            'event_id' => $request->event_id,
            'payment_id' => $request->payment_id,
            'user_id' => $request->user_id,
        ]);
        try{
            $event = $this->createEventLogDto($request);
            $this->webhookService->receivePayment($event);
            return response()->json(['message' => 'ok'], 200);
        }catch(\Exception $e){
            Log::error($e->getMessage());
            return response()->json(['message' => 'error'], 500);
        }
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
        return response()->json($this->webhookService->getPaymentEvents($paymentId));
    }
}
