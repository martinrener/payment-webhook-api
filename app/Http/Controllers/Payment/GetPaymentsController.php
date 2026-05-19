<?php

namespace App\Http\Controllers\Payment;

use Illuminate\Http\Request;
use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;

class GetPaymentsController extends Controller
{
    public function __construct(
        private WebhookService $webhookService,
    ) {}

    public function __invoke(Request $request): JsonResponse
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
}
