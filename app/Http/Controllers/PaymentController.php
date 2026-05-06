<?php

namespace App\Http\Controllers;

use App\Services\WebhookService;
use Illuminate\Support\Facades\Gate;

class PaymentController extends Controller
{
    public function __construct(
        private WebhookService $webhookService,
    ) {}

    public function index()
    {
        Gate::authorize('access-admin');
        $payments = $this->webhookService->getPayments();
        return view('payments', ['payments' => $payments]);
    }
}
