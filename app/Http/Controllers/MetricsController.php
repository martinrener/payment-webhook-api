<?php

namespace App\Http\Controllers;

use App\Services\MetricsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class MetricsController extends Controller
{
    public function __construct(
        private MetricsService $metricsService,
    ) {}
    public function index(): JsonResponse
    {
        Gate::authorize('access-admin');
        return response()->json($this->metricsService->getMetrics());
    }
}
