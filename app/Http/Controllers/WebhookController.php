<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreWebhookRequest;
use App\Jobs\ProcessWebhookPayment;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function exportPayments(Request $request): StreamedResponse
    {
        Gate::authorize('access-admin');
        $event = $request->query('event');
        $user_id = $request->query('user_id');
        $currency = $request->query('currency');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $data = $this->webhookService->exportPayments($event, $user_id, $currency, $dateFrom, $dateTo);

        return response()->streamDownload(function () use ($data) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID', 'Event', 'Amount', 'Currency', 'User', 'Date']); // headers
            foreach ($data as $row) {
                fputcsv($output, [$row['payment_id'], $row['event'], $row['amount'], $row['currency'], $row['user_id'], $row['created_at']]);
            }
            fclose($output);
        }, 'payments.csv');    
    }
}
