<?php

namespace App\Http\Controllers\Payment;

use Illuminate\Http\Request;
use App\Services\WebhookService;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Services\ExportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportPaymentsController extends Controller
{
    public function __construct(
        private WebhookService $webhookService,
        private ExportService $exportService,
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

        return $this->exportService->exportPayments($data);
    }
}
