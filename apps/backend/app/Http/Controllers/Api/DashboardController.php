<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Queries\DashboardMetricsQuery;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __invoke(DashboardMetricsQuery $metrics): JsonResponse
    {
        return response()->json(['data' => $metrics->get()]);
    }
}
