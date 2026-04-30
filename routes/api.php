<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\AuthController;
use GuzzleHttp\Middleware;
use App\Http\Controllers\AdminController;

Route::post('/login',[AuthController::class,'login']); 

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout',[AuthController::class,'logout']);
    Route::get('/user',[AuthController::class,'user']);
    Route::get('/payments',[WebhookController::class,'getPayments']);
    Route::get('/payments/{payment_id}/events',[WebhookController::class,'getPaymentEvents']);
    Route::post('/admin/refund', [AdminController::class, 'refundPayment']);
});

Route::post('/webhooks/payment',[WebhookController::class,'store']);