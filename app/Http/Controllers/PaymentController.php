<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WebhookService;

class PaymentController extends Controller
{
    public function __construct(
        private WebhookService $webhook_service,
    ) {}
    
    public function index()
    {
        $payments = $this->webhook_service->getPayments();
        return view('payments', ['payments' => $payments]);
    }
}
