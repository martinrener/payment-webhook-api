<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\AuthController;
use GuzzleHttp\Middleware;

Route::post('/login',[AuthController::class,'login']); 
Route::post('/logout',[AuthController::class,'logout'])->middleware('auth');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user',[AuthController::class,'user']);
    Route::get('/payments',[WebhookController::class,'getPayments']);
    Route::get('/payments/{payment_id}/events',[WebhookController::class,'getPaymentEvents']);
});

Route::post('/webhooks/payment',[WebhookController::class,'store']);