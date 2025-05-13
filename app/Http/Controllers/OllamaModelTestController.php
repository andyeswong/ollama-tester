<?php

namespace App\Http\Controllers;

use App\Models\OllamaServer;
use App\Models\OllamaModelTest;
use App\Services\OllamaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OllamaModelTestController extends Controller
{
    protected OllamaService $ollamaService;

    public function __construct(OllamaService $ollamaService)
    {
        $this->ollamaService = $ollamaService;
    }

    /**
     * Show the form for creating a new test.
     */
    public function create(OllamaServer $server): View
    {
        $models = $this->ollamaService->getModels($server);
        
        return view('ollama.tests.create', compact('server', 'models'));
    }

    /**
     * Store a newly created test in storage.
     */
    public function store(Request $request, OllamaServer $server): RedirectResponse
    {
        $validated = $request->validate([
            'model_name' => 'required|string|max:255',
            'prompt' => 'required|string',
            'iterations' => 'required|integer|min:1|max:50',
            'run_mode' => 'required|in:sequential,parallel',
        ]);

        // Create a single test group ID for all iterations
        $testGroupId = uniqid('test_group_');
        
        // Create multiple test instances
        $testIds = [];
        for ($i = 0; $i < $validated['iterations']; $i++) {
            $test = OllamaModelTest::create([
                'ollama_server_id' => $server->id,
                'model_name' => $validated['model_name'],
                'prompt' => $validated['prompt'],
                'status' => 'pending',
                'metadata' => [
                    'run_mode' => $validated['run_mode'],
                    'test_group' => $testGroupId,
                    'iteration' => $i + 1,
                    'total_iterations' => $validated['iterations']
                ],
            ]);
            
            $testIds[] = $test->id;
        }

        // Run the tests in the selected mode
        if ($validated['run_mode'] === 'parallel') {
            $this->ollamaService->runParallelTests($testIds);
            $message = 'Ollama model tests started in parallel mode (stress test).';
        } else {
            $this->ollamaService->runSequentialTests($testIds);
            $message = 'Ollama model tests started in sequential mode.';
        }

        return redirect()
            ->route('ollama.tests.index', $server)
            ->with('success', $message);
    }

    /**
     * Display a listing of the tests for a server.
     */
    public function index(OllamaServer $server): View
    {
        $tests = $server->tests()->latest()->get();
        
        return view('ollama.tests.index', compact('server', 'tests'));
    }

    /**
     * Display the specified test.
     */
    public function show(OllamaServer $server, OllamaModelTest $test): View
    {
        return view('ollama.tests.show', compact('server', 'test'));
    }

    /**
     * Delete the specified test
     */
    public function destroy(OllamaServer $server, OllamaModelTest $test): RedirectResponse
    {
        $test->delete();

        return redirect()
            ->route('ollama.tests.index', $server)
            ->with('success', 'Test deleted successfully.');
    }

    /**
     * Test multiple models at once with the same prompt
     */
    public function createMultipleModels(OllamaServer $server): View
    {
        $models = $this->ollamaService->getModels($server);
        
        return view('ollama.tests.create-multiple', compact('server', 'models'));
    }

    /**
     * Store tests for multiple models
     */
    public function storeMultipleModels(Request $request, OllamaServer $server): RedirectResponse
    {
        $validated = $request->validate([
            'model_names' => 'required|array',
            'model_names.*' => 'string|max:255',
            'prompt' => 'required|string',
            'iterations' => 'required|integer|min:1|max:10',
            'run_mode' => 'required|in:sequential,parallel',
        ]);

        // Create a single test group ID for all models and iterations
        $testGroupId = uniqid('test_group_');
        $testIds = [];
        foreach ($validated['model_names'] as $modelName) {
            for ($i = 0; $i < $validated['iterations']; $i++) {
                $test = OllamaModelTest::create([
                    'ollama_server_id' => $server->id,
                    'model_name' => $modelName,
                    'prompt' => $validated['prompt'],
                    'status' => 'pending',
                    'metadata' => [
                        'run_mode' => $validated['run_mode'],
                        'test_group' => $testGroupId,
                        'iteration' => $i + 1,
                        'total_iterations' => $validated['iterations'],
                        'model_group' => true
                    ],
                ]);
                
                $testIds[] = $test->id;
            }
        }

        // Run the tests in the selected mode
        if ($validated['run_mode'] === 'parallel') {
            $this->ollamaService->runParallelTests($testIds);
            $message = 'Tests for multiple models started in parallel mode (stress test).';
        } else {
            $this->ollamaService->runSequentialTests($testIds);
            $message = 'Tests for multiple models started in sequential mode.';
        }

        return redirect()
            ->route('ollama.tests.index', $server)
            ->with('success', $message);
    }

    /**
     * Show results comparison between different model tests
     */
    public function compareResults(OllamaServer $server, Request $request): View
    {
        $testIds = $request->input('test_ids', []);
        
        // Ensure we have at least one test ID
        if (empty($testIds)) {
            return view('ollama.tests.compare', [
                'server' => $server,
                'tests' => collect(),
            ]);
        }
        
        // Fetch tests and make sure they belong to this server
        $tests = OllamaModelTest::whereIn('id', $testIds)
            ->where('ollama_server_id', $server->id)
            ->get();
            
        // Get server metrics for these tests - using a simpler approach for SQLite compatibility
        $serverMetrics = \App\Models\ServerMetric::where('ollama_server_id', $server->id)
            ->get()
            ->filter(function($metric) use ($testIds) {
                // Check if any of the testIds are in the active_tests array
                if (!is_array($metric->active_tests)) {
                    return false;
                }
                
                foreach ($testIds as $testId) {
                    if (in_array($testId, $metric->active_tests)) {
                        return true;
                    }
                }
                
                return false;
            });
            
        // Calculate average metrics per test
        $testMetricsAvg = [];
        foreach ($testIds as $testId) {
            $metricsForTest = $serverMetrics->filter(function($metric) use ($testId) {
                return in_array($testId, $metric->active_tests);
            });
            
            if ($metricsForTest->count() > 0) {
                $testMetricsAvg[$testId] = [
                    'cpu_usage' => $metricsForTest->avg('cpu_usage'),
                    'memory_usage_percent' => $metricsForTest->avg('memory_usage_percent'),
                    'gpu_utilization' => $metricsForTest->whereNotNull('gpu_utilization')->avg('gpu_utilization'),
                    'gpu_memory_utilization' => $metricsForTest->whereNotNull('gpu_memory_utilization')->avg('gpu_memory_utilization'),
                    'metrics_count' => $metricsForTest->count(),
                    'max_cpu_usage' => $metricsForTest->max('cpu_usage'),
                    'max_memory_usage' => $metricsForTest->max('memory_usage_percent'),
                    'max_gpu_utilization' => $metricsForTest->whereNotNull('gpu_utilization')->max('gpu_utilization'),
                    'max_gpu_memory_utilization' => $metricsForTest->whereNotNull('gpu_memory_utilization')->max('gpu_memory_utilization'),
                ];
            }
        }
        
        return view('ollama.tests.compare', compact('server', 'tests', 'serverMetrics', 'testMetricsAvg'));
    }
} 