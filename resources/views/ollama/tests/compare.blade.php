@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
            <a href="{{ route('ollama.tests.index', $server) }}" class="text-blue-500 hover:underline mr-4">
                &larr; Back to Tests
            </a>
            <h1 class="text-2xl font-semibold text-gray-800">Compare Test Results</h1>
        </div>
        
        @if(count($tests) >= 2)
        <div>
            <a href="{{ route('ollama.tests.compare.download-csv', ['server' => $server, 'test_ids' => request()->input('test_ids')]) }}" 
               class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Download CSV
            </a>
        </div>
        @endif
    </div>

    @if(count($tests) < 2)
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
        <div class="flex">
            <div class="ml-3">
                <p class="text-sm text-yellow-700">
                    You need to select at least two tests to compare. <a href="{{ route('ollama.tests.index', $server) }}" class="text-blue-600 hover:underline">Go back</a> and select more tests.
                </p>
            </div>
        </div>
    </div>
    @else
    <div class="mb-6">
        <div class="glass-card overflow-hidden">
            <div class="px-6 py-4 border-b border-glass-border">
                <h2 class="text-xl font-semibold neon-text-primary">Performance Comparison</h2>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-glass-border">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-foreground/70">Metric</th>
                                @foreach($tests as $test)
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-foreground/70">
                                    {{ $test->model_name }}
                                    <div class="text-xs font-normal normal-case opacity-70">Test #{{ $test->id }}</div>
                                </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-glass-border">
                            <tr class="hover:bg-glass-bg/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">Status</td>
                                @foreach($tests as $test)
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($test->status === 'completed')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                        Completed
                                    </span>
                                    @elseif($test->status === 'failed')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                        Failed
                                    </span>
                                    @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                                        Pending
                                    </span>
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                            <tr class="hover:bg-glass-bg/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">Response Time</td>
                                @foreach($tests as $test)
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{ $test->response_time ? number_format($test->response_time, 2) . 's' : '-' }}
                                </td>
                                @endforeach
                            </tr>
                            
                            @php
                                $allMetrics = [];
                                foreach ($tests as $test) {
                                    if ($test->metadata) {
                                        foreach ($test->metadata as $key => $value) {
                                            $allMetrics[$key] = true;
                                        }
                                    }
                                }
                                $allMetrics = array_keys($allMetrics);
                            @endphp
                            
                            @foreach($allMetrics as $metric)
                            <tr class="hover:bg-glass-bg/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ Str::title(str_replace('_', ' ', $metric)) }}</td>
                                @foreach($tests as $test)
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if(isset($test->metadata[$metric]))
                                        @if(is_numeric($test->metadata[$metric]))
                                            {{ number_format($test->metadata[$metric], 4) }}
                                            @if(Str::contains($metric, 'duration'))
                                             s
                                            @endif
                                        @else
                                            {{ $test->metadata[$metric] }}
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Server Metrics Section -->
    <div class="mb-6">
        <div class="glass-card overflow-hidden">
            <div class="px-6 py-4 border-b border-glass-border">
                <h2 class="text-xl font-semibold neon-text-primary">Server Resource Usage</h2>
                <p class="text-sm text-foreground/70">Average resource utilization during test execution</p>
            </div>
            
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-glass-border">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-foreground/70">Resource</th>
                                @foreach($tests as $test)
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-foreground/70">
                                    {{ $test->model_name }}
                                    <div class="text-xs font-normal normal-case opacity-70">Test #{{ $test->id }}</div>
                                </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-glass-border">
                            <!-- CPU Usage -->
                            <tr class="hover:bg-glass-bg/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">CPU Usage</td>
                                @foreach($tests as $test)
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if(isset($testMetricsAvg[$test->id]['cpu_usage']))
                                        <div class="flex items-center">
                                            <div class="w-full bg-foreground/10 rounded-full h-2.5 mr-2">
                                                <div class="bg-blue-600/70 h-2.5 rounded-full" style="width: {{ min(100, $testMetricsAvg[$test->id]['cpu_usage']) }}%"></div>
                                            </div>
                                            <span>{{ number_format($testMetricsAvg[$test->id]['cpu_usage'], 1) }}%</span>
                                        </div>
                                        <div class="text-xs opacity-70 mt-1">
                                            Peak: {{ number_format($testMetricsAvg[$test->id]['max_cpu_usage'], 1) }}%
                                        </div>
                                    @else
                                        <span class="opacity-50">No data</span>
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                            
                            <!-- Memory Usage -->
                            <tr class="hover:bg-glass-bg/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">Memory Usage</td>
                                @foreach($tests as $test)
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if(isset($testMetricsAvg[$test->id]['memory_usage_percent']))
                                        <div class="flex items-center">
                                            <div class="w-full bg-foreground/10 rounded-full h-2.5 mr-2">
                                                <div class="bg-green-600/70 h-2.5 rounded-full" style="width: {{ min(100, $testMetricsAvg[$test->id]['memory_usage_percent']) }}%"></div>
                                            </div>
                                            <span>{{ number_format($testMetricsAvg[$test->id]['memory_usage_percent'], 1) }}%</span>
                                        </div>
                                        <div class="text-xs opacity-70 mt-1">
                                            Peak: {{ number_format($testMetricsAvg[$test->id]['max_memory_usage'], 1) }}%
                                        </div>
                                    @else
                                        <span class="opacity-50">No data</span>
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                            
                            <!-- GPU Utilization (if available) -->
                            <tr class="hover:bg-glass-bg/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">GPU Utilization</td>
                                @foreach($tests as $test)
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if(isset($testMetricsAvg[$test->id]['gpu_utilization']) && $testMetricsAvg[$test->id]['gpu_utilization'] !== null)
                                        <div class="flex items-center">
                                            <div class="w-full bg-foreground/10 rounded-full h-2.5 mr-2">
                                                <div class="bg-purple-600/70 h-2.5 rounded-full" style="width: {{ min(100, $testMetricsAvg[$test->id]['gpu_utilization']) }}%"></div>
                                            </div>
                                            <span>{{ number_format($testMetricsAvg[$test->id]['gpu_utilization'], 1) }}%</span>
                                        </div>
                                        <div class="text-xs opacity-70 mt-1">
                                            Peak: {{ number_format($testMetricsAvg[$test->id]['max_gpu_utilization'], 1) }}%
                                        </div>
                                    @else
                                        <span class="opacity-50">Not available</span>
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                            
                            <!-- GPU Memory (if available) -->
                            <tr class="hover:bg-glass-bg/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">GPU Memory</td>
                                @foreach($tests as $test)
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if(isset($testMetricsAvg[$test->id]['gpu_memory_utilization']) && $testMetricsAvg[$test->id]['gpu_memory_utilization'] !== null)
                                        <div class="flex items-center">
                                            <div class="w-full bg-foreground/10 rounded-full h-2.5 mr-2">
                                                <div class="bg-red-600/70 h-2.5 rounded-full" style="width: {{ min(100, $testMetricsAvg[$test->id]['gpu_memory_utilization']) }}%"></div>
                                            </div>
                                            <span>{{ number_format($testMetricsAvg[$test->id]['gpu_memory_utilization'], 1) }}%</span>
                                        </div>
                                        <div class="text-xs opacity-70 mt-1">
                                            Peak: {{ number_format($testMetricsAvg[$test->id]['max_gpu_memory_utilization'], 1) }}%
                                        </div>
                                    @else
                                        <span class="opacity-50">Not available</span>
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                            
                            <!-- Sample Count -->
                            <tr class="hover:bg-glass-bg/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">Metrics Samples</td>
                                @foreach($tests as $test)
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if(isset($testMetricsAvg[$test->id]['metrics_count']))
                                        {{ $testMetricsAvg[$test->id]['metrics_count'] }} samples
                                    @else
                                        <span class="opacity-50">No data</span>
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div>
        <div class="glass-card overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-glass-border">
                <h2 class="text-xl font-semibold neon-text-primary">Prompt</h2>
            </div>
            <div class="p-6">
                <div class="bg-foreground/5 backdrop-blur-sm p-4 rounded-lg whitespace-pre-wrap">{{ $tests->first()->prompt }}</div>
            </div>
        </div>
        
        <div class="glass-card overflow-hidden">
            <div class="px-6 py-4 border-b border-glass-border">
                <h2 class="text-xl font-semibold neon-text-primary">Responses</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($tests as $test)
                    <div class="glass border border-glass-border rounded-lg overflow-hidden">
                        <div class="bg-foreground/5 backdrop-blur-sm px-4 py-2 border-b border-glass-border">
                            <h3 class="font-medium">{{ $test->model_name }}</h3>
                            <div class="text-xs opacity-70">Response Time: {{ $test->response_time ? number_format($test->response_time, 2) . 's' : '-' }}</div>
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold text-lg mb-2 neon-text-primary">{{ $test->model_name }}</h3>
                            <div class="text-sm mb-4 opacity-70">{{ \Illuminate\Support\Str::limit($test->prompt, 100) }}</div>
                            
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <span class="text-foreground/70 text-sm">Status:</span>
                                    <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($test->status === 'completed') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300
                                        @elseif($test->status === 'failed') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300
                                        @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300 @endif">
                                        {{ ucfirst($test->status) }}
                                    </span>
                                </div>
                                
                                <div>
                                    <span class="text-foreground/70 text-sm">Response Time:</span>
                                    <span class="ml-2 font-medium">{{ $test->response_time ? number_format($test->response_time, 2) . 's' : '-' }}</span>
                                </div>
                            </div>
                            
                            @if(isset($test->metadata['run_mode']))
                            <div class="mb-4">
                                <span class="text-foreground/70 text-sm">Run Mode:</span>
                                <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if($test->metadata['run_mode'] === 'sequential') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300
                                    @else bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300 @endif">
                                    {{ ucfirst($test->metadata['run_mode']) }}
                                    @if($test->metadata['run_mode'] === 'parallel')
                                    (Stress Test)
                                    @endif
                                </span>
                            </div>
                            @endif
                            
                            <div class="text-sm mt-4">
                            @if($test->status === 'completed')
                            <div class="whitespace-pre-wrap">{{ $test->response }}</div>
                            @elseif($test->status === 'failed')
                            <div class="text-red-600 dark:text-red-400 whitespace-pre-wrap">{{ $test->response }}</div>
                            @else
                            <div class="text-yellow-600 dark:text-yellow-400">Waiting for response...</div>
                            @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection 