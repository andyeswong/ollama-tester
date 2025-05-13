<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ServerMetricsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Server Metrics API Endpoints
Route::post('/server-metrics', [ServerMetricsController::class, 'store']);
Route::get('/server-metrics/server/{serverId}', [ServerMetricsController::class, 'getServerMetrics']);
Route::get('/server-metrics/test/{testId}', [ServerMetricsController::class, 'getTestMetrics']); 