<?php

namespace App\Http\Controllers\Payment;

use Illuminate\Http\Request;
use App\Services\WebhookService;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportPaymentsController extends Controller
{
    public function __construct(
        private WebhookService $webhookService,
    ) {}

    public function __invoke(Request $request): StreamedResponse
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
