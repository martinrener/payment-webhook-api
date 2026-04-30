<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function __construct(
        private WebhookService $webhookService,
    ) {}

    public function refundPayment(Request $request): JsonResponse
    {
        $paymentId = $request->input('payment_id');
        try {
            $this->webhookService->refundPayment($paymentId);
            return response()->json(['message' => 'Refund processed'], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'Error processing refund'], 500);
        }
    }
}
