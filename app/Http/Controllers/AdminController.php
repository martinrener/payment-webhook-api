<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use App\DTOs\EventLogDto;
use App\Http\Requests\StoreWebhookRequest;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function __construct(
        private WebhookService $webhookService,
    ) {}

    public function refundPayment(string $paymentId): JsonResponse
    {
        // Implement refund logic here, e.g. call payment gateway API to process refund
        return response()->json(['message' => 'Refund processed'], 200);
    }
}
