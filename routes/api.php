<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;

Route::get('/payments',[WebhookController::class,'getPayments']);

Route::get('/payments/{payment_id}/events',[WebhookController::class,'getPaymentEvents']);

Route::post('/webhooks/payment',[WebhookController::class,'store']);