<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OllamaServer;
use App\Models\ServerMetric;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ServerMetricsController extends Controller
{
    /**
     * Store a new server metric.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Validate incoming data
            $validator = Validator::make($request->all(), [
                'server_id' => 'required|exists:ollama_servers,id',
                'timestamp' => 'nullable|date',
                'active_tests' => 'nullable|array',
                'cpu' => 'required|array',
                'memory' => 'required|array',
                'temperatures' => 'nullable|array',
                'gpu' => 'nullable|array',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Get validated data
            $data = $validator->validated();
            
            // Create new server metric
            $metric = new ServerMetric();
            $metric->ollama_server_id = $data['server_id'];
            $metric->active_tests = $data['active_tests'] ?? [];
            $metric->collected_at = $data['timestamp'] ?? Carbon::now();
            
            // CPU metrics
            if (isset($data['cpu'])) {
                $metric->cpu_usage = $data['cpu']['usage'] ?? null;
                $metric->cpu_cores = $data['cpu']['cores'] ?? null;
            }
            
            // Memory metrics
            if (isset($data['memory'])) {
                $metric->memory_total = $data['memory']['total'] ?? null;
                $metric->memory_used = $data['memory']['used'] ?? null;
                $metric->memory_usage_percent = $data['memory']['usage_percent'] ?? null;
            }
            
            // Temperature metrics
            if (isset($data['temperatures'])) {
                $metric->cpu_temperature = $data['temperatures']['cpu'] ?? null;
            }
            
            // GPU metrics
            if (isset($data['gpu']) && $data['gpu']) {
                $metric->gpu_utilization = $data['gpu']['gpu_utilization'] ?? null;
                $metric->gpu_memory_utilization = $data['gpu']['memory_utilization'] ?? null;
                $metric->gpu_memory_total = $data['gpu']['memory_total'] ?? null;
                $metric->gpu_memory_free = $data['gpu']['memory_free'] ?? null;
                $metric->gpu_memory_used = $data['gpu']['memory_used'] ?? null;
                $metric->gpu_temperature = $data['gpu']['temperature'] ?? null;
            }
            
            // Store the raw data for future reference
            $metric->raw_data = $request->all();
            
            // Save the metric
            $metric->save();
            
            // Update server's last_metrics_at timestamp
            $server = OllamaServer::find($data['server_id']);
            if ($server) {
                $server->last_metrics_at = Carbon::now();
                $server->save();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Server metrics stored successfully',
                'metric_id' => $metric->id
            ]);
        } catch (\Exception $e) {
            Log::error('Error storing server metrics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while storing server metrics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get metrics for a specific server.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $serverId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getServerMetrics(Request $request, $serverId)
    {
        try {
            $limit = $request->input('limit', 100);
            $timeframe = $request->input('timeframe', '1h'); // 1h, 6h, 24h, 7d
            
            $query = ServerMetric::forServer($serverId)
                ->orderBy('collected_at', 'desc');
            
            // Apply timeframe filter
            if ($timeframe) {
                $timeAgo = null;
                
                switch ($timeframe) {
                    case '1h':
                        $timeAgo = Carbon::now()->subHour();
                        break;
                    case '6h':
                        $timeAgo = Carbon::now()->subHours(6);
                        break;
                    case '24h':
                        $timeAgo = Carbon::now()->subDay();
                        break;
                    case '7d':
                        $timeAgo = Carbon::now()->subDays(7);
                        break;
                }
                
                if ($timeAgo) {
                    $query->where('collected_at', '>=', $timeAgo);
                }
            }
            
            $metrics = $query->limit($limit)->get();
            
            return response()->json([
                'success' => true,
                'metrics' => $metrics
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving server metrics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving server metrics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get metrics for a specific test.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $testId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTestMetrics(Request $request, $testId)
    {
        try {
            $metrics = ServerMetric::forTests([$testId])
                ->orderBy('collected_at', 'asc')
                ->get();
            
            return response()->json([
                'success' => true,
                'metrics' => $metrics
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving test metrics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving test metrics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
