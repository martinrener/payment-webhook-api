<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WebhookService;

class PaymentController extends Controller
{
    public function __construct(
        private WebhookService $webhookService,
    ) {}

    public function index()
    {
        $payments = $this->webhookService->getPayments();
        return view('payments', ['payments' => $payments]);
    }
}
