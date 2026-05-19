<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MetricsController;

Route::post('/login',[AuthController::class,'login'])->middleware('throttle:5,15'); 

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout',[AuthController::class,'logout']);
    Route::get('/user',[AuthController::class,'user']);
    Route::post('/admin/refund', [AdminController::class, 'refundPayment']);
    Route::get('/metrics', [MetricsController::class, 'index']);
    Route::get('/payments',[WebhookController::class,'getPayments']);
    Route::get('/payments/export', [WebhookController::class, 'exportPayments']);
    Route::get('/payments/{payment_id}/events',[WebhookController::class,'getPaymentEvents']);
});

Route::post('/webhooks/payment',[WebhookController::class,'store'])->middleware('throttle:100,1');

Route::get('/health', function () {
    return response()->json(['status' => 'ok'], 200);
});