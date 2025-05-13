<?php

namespace App\Services;

use App\Models\OllamaServer;
use App\Models\OllamaModelTest;
use App\Events\TestStatusUpdated;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Promise\Utils;
use GuzzleHttp\Client;
use Pusher\Pusher;

class OllamaService
{
    protected $pusher;

    public function __construct()
    {
        // Initialize Pusher client for direct broadcasting
        $this->initPusher();
    }

    /**
     * Initialize Pusher client
     */
    protected function initPusher()
    {
        try {
            $this->pusher = new Pusher(
                config('broadcasting.connections.pusher.key'),
                config('broadcasting.connections.pusher.secret'),
                config('broadcasting.connections.pusher.app_id'),
                config('broadcasting.connections.pusher.options', [])
            );
        } catch (\Exception $e) {
            Log::error('Failed to initialize Pusher client', [
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Broadcast a test status update directly via Pusher
     *
     * @param OllamaModelTest $test
     * @param string $status
     * @return void
     */
    protected function broadcastTestStatus(OllamaModelTest $test, string $status)
    {
        // First, use Laravel's event system
        event(new TestStatusUpdated($test, $status));

        // Also broadcast directly using Pusher as a fallback
        try {
            if ($this->pusher) {
                $channelName = 'server.' . $test->ollama_server_id;
                $eventName = 'App\\Events\\TestStatusUpdated';
                $data = [
                    'id' => $test->id,
                    'status' => $status,
                    'response_time' => $test->response_time,
                    'updated_at' => $test->updated_at->toIso8601String(),
                    'test_group' => $test->metadata['test_group'] ?? null,
                ];

                $this->pusher->trigger($channelName, $eventName, $data);
                
                // Log successful direct broadcast
                Log::info("Directly broadcasted test status via Pusher", [
                    'test_id' => $test->id,
                    'status' => $status,
                    'channel' => $channelName
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to broadcast test status via Pusher', [
                'test_id' => $test->id,
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get available models from an Ollama server
     *
     * @param OllamaServer $server
     * @return array
     */
    public function getModels(OllamaServer $server): array
    {
        try {
            $response = Http::timeout(10)
                ->get($server->url . '/api/tags');

            if ($response->successful()) {
                return $response->json('models', []);
            }

            Log::error('Failed to fetch models from Ollama server', [
                'server' => $server->name,
                'url' => $server->url,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Exception when fetching models from Ollama server', [
                'server' => $server->name,
                'url' => $server->url,
                'exception' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Run a single test against an Ollama model
     *
     * @param OllamaModelTest $test
     * @return bool
     */
    public function runTest(OllamaModelTest $test): bool
    {
        try {
            $server = $test->server;
            $startTime = microtime(true);
            
            // Update status to in_progress and broadcast
            $test->update(['status' => 'in_progress']);
            $this->broadcastTestStatus($test, 'in_progress');

            $response = Http::timeout(30)
                ->post($server->url . '/api/generate', [
                    'model' => $test->model_name,
                    'prompt' => $test->prompt,
                    'system' => 'You are a helpful AI assistant',
                    'stream' => false,
                ]);

            $endTime = microtime(true);
            $responseTime = $endTime - $startTime;

            if ($response->successful()) {
                // Preserve the existing metadata and merge with new performance data
                $existingMetadata = $test->metadata ?? [];
                $performanceData = [
                    'total_duration' => $response->json('total_duration'),
                    'load_duration' => $response->json('load_duration'),
                    'prompt_eval_count' => $response->json('prompt_eval_count'),
                    'prompt_eval_duration' => $response->json('prompt_eval_duration'),
                    'eval_count' => $response->json('eval_count'),
                    'eval_duration' => $response->json('eval_duration'),
                ];
                
                $mergedMetadata = array_merge($existingMetadata, $performanceData);

                $test->update([
                    'response' => $response->json('response', ''),
                    'response_time' => $responseTime,
                    'metadata' => $mergedMetadata,
                    'status' => 'completed',
                ]);
                
                // Broadcast test completed event
                $this->broadcastTestStatus($test, 'completed');

                return true;
            }

            // Preserve existing metadata on failure too
            $existingMetadata = $test->metadata ?? [];
            
            $test->update([
                'response' => $response->body(),
                'response_time' => $responseTime,
                'status' => 'failed',
                'metadata' => $existingMetadata,
            ]);
            
            // Broadcast test failed event
            $this->broadcastTestStatus($test, 'failed');

            Log::error('Failed to run Ollama model test', [
                'server' => $server->name,
                'model' => $test->model_name,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            // Preserve existing metadata on exception
            $existingMetadata = $test->metadata ?? [];
            
            $test->update([
                'response' => $e->getMessage(),
                'status' => 'failed',
                'metadata' => $existingMetadata,
            ]);
            
            // Broadcast test failed event
            $this->broadcastTestStatus($test, 'failed');

            Log::error('Exception when running Ollama model test', [
                'test_id' => $test->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Run tests sequentially (one after another)
     *
     * @param array $testIds
     * @return void
     */
    public function runSequentialTests(array $testIds): void
    {
        $tests = OllamaModelTest::whereIn('id', $testIds)->get();
        
        foreach ($tests as $test) {
            dispatch(function () use ($test) {
                $this->runTest($test);
            })->onQueue('ollama-tests');
        }
    }

    /**
     * Run multiple tests in parallel for server stress testing
     *
     * @param array $testIds
     * @return void
     */
    public function runParallelTests(array $testIds): void
    {
        $tests = OllamaModelTest::whereIn('id', $testIds)->get();
        
        if ($tests->isEmpty()) {
            return;
        }
        
        // Group tests by server to run them in parallel batches
        $testsByServer = $tests->groupBy('ollama_server_id');
        
        foreach ($testsByServer as $serverId => $serverTests) {
            dispatch(function () use ($serverTests) {
                $this->executeParallelTests($serverTests);
            })->onQueue('ollama-tests');
        }
    }
    
    /**
     * Execute tests in parallel using HTTP client's promise functionality
     *
     * @param \Illuminate\Support\Collection $tests
     * @return void
     */
    protected function executeParallelTests($tests): void
    {
        if ($tests->isEmpty()) {
            return;
        }
        
        $server = $tests->first()->server;
        $client = new Client([
            'base_uri' => $server->url,
            'timeout' => 60,
        ]);
        
        $promises = [];
        
        foreach ($tests as $test) {
            // Mark test as in progress
            $test->update(['status' => 'in_progress']);
            // Broadcast status update
            $this->broadcastTestStatus($test, 'in_progress');
            
            $startTime = microtime(true);
            $promises[$test->id] = $client->postAsync('/api/generate', [
                'json' => [
                    'model' => $test->model_name,
                    'prompt' => $test->prompt,
                    'system' => 'You are a helpful AI assistant',
                    'stream' => false,
                ],
            ])->then(
                function ($response) use ($test, $startTime) {
                    $endTime = microtime(true);
                    $responseTime = $endTime - $startTime;
                    $responseBody = json_decode($response->getBody(), true);
                    
                    // Preserve the existing metadata and merge with new data
                    $existingMetadata = $test->metadata ?? [];
                    $performanceData = [
                        'total_duration' => $responseBody['total_duration'] ?? null,
                        'load_duration' => $responseBody['load_duration'] ?? null,
                        'prompt_eval_count' => $responseBody['prompt_eval_count'] ?? null,
                        'prompt_eval_duration' => $responseBody['prompt_eval_duration'] ?? null,
                        'eval_count' => $responseBody['eval_count'] ?? null,
                        'eval_duration' => $responseBody['eval_duration'] ?? null,
                        'simultaneous_test' => true,
                    ];
                    
                    $mergedMetadata = array_merge($existingMetadata, $performanceData);
                    
                    $test->update([
                        'response' => $responseBody['response'] ?? '',
                        'response_time' => $responseTime,
                        'metadata' => $mergedMetadata,
                        'status' => 'completed',
                    ]);
                    
                    // Broadcast test completed event
                    $this->broadcastTestStatus($test, 'completed');
                },
                function ($exception) use ($test) {
                    // Preserve existing metadata on exception
                    $existingMetadata = $test->metadata ?? [];
                    
                    $test->update([
                        'response' => $exception->getMessage(),
                        'status' => 'failed',
                        'metadata' => $existingMetadata,
                    ]);
                    
                    // Broadcast test failed event
                    $this->broadcastTestStatus($test, 'failed');
                }
            );
        }
        
        // Wait for all promises to complete
        try {
            Utils::settle($promises)->wait();
        } catch (\Exception $e) {
            Log::error('Exception when running parallel Ollama tests', [
                'exception' => $e->getMessage(),
            ]);
        }
    }
} 