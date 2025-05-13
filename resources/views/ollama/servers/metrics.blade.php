@extends('layouts.app')

@section('content')
<div class="bg-white shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold">Server Metrics - {{ $server->name }}</h2>
            <div class="flex items-center space-x-2">
                <select id="timeframeSelector" class="px-4 py-2 bg-white border border-gray-300 rounded text-sm">
                    <option value="15m">Last 15 minutes</option>
                    <option value="1h" selected>Last hour</option>
                    <option value="6h">Last 6 hours</option>
                    <option value="24h">Last 24 hours</option>
                </select>
                <a href="{{ route('ollama.servers.show', $server) }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded">
                    <span>Back to Server</span>
                </a>
                <button id="refreshBtn" class="px-4 py-2 bg-indigo-600 text-white hover:bg-indigo-700 rounded">
                    <span>Refresh</span>
                </button>
            </div>
        </div>

        <!-- Debug Tools -->
        <div class="bg-gray-50 p-4 rounded-lg border mb-6">
            <h3 class="text-lg font-medium mb-2">Debug Tools</h3>
            <div class="flex space-x-3">
                <a href="{{ route('ollama.servers.test-pusher', $server) }}" target="_blank" class="px-3 py-2 bg-purple-600 text-white hover:bg-purple-700 rounded text-sm">
                    Test Event System
                </a>
                <a href="{{ route('ollama.servers.trigger-monitor', $server) }}" target="_blank" class="px-3 py-2 bg-green-600 text-white hover:bg-green-700 rounded text-sm">
                    Trigger Monitor Directly
                </a>
                <button id="copyPusherDetailsBtn" class="px-3 py-2 bg-gray-600 text-white hover:bg-gray-700 rounded text-sm">
                    Copy Pusher Details
                </button>
            </div>
            <div class="mt-2 text-xs text-gray-500">
                Use these tools to test the event system and manually trigger monitoring.
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- CPU Usage Card -->
            <div class="bg-white p-4 rounded-lg shadow border">
                <h3 class="text-sm font-medium text-gray-500 mb-2">CPU Usage</h3>
                <div class="flex items-center">
                    <div class="text-2xl font-bold" id="cpu-usage">--</div>
                    <div class="ml-2 text-sm text-gray-500">%</div>
                </div>
                <div class="mt-2 h-2 bg-gray-200 rounded-full">
                    <div id="cpu-progress" class="h-2 bg-blue-500 rounded-full" style="width: 0%"></div>
                </div>
            </div>

            <!-- Memory Usage Card -->
            <div class="bg-white p-4 rounded-lg shadow border">
                <h3 class="text-sm font-medium text-gray-500 mb-2">Memory Usage</h3>
                <div class="flex items-center">
                    <div class="text-2xl font-bold" id="memory-usage">--</div>
                    <div class="ml-2 text-sm text-gray-500">%</div>
                </div>
                <div class="mt-2 h-2 bg-gray-200 rounded-full">
                    <div id="memory-progress" class="h-2 bg-green-500 rounded-full" style="width: 0%"></div>
                </div>
                <div class="mt-1 text-xs text-gray-500" id="memory-details">-- / --</div>
            </div>

            <!-- GPU Usage Card -->
            <div class="bg-white p-4 rounded-lg shadow border">
                <h3 class="text-sm font-medium text-gray-500 mb-2">GPU Usage</h3>
                <div class="flex items-center">
                    <div class="text-2xl font-bold" id="gpu-usage">--</div>
                    <div class="ml-2 text-sm text-gray-500">%</div>
                </div>
                <div class="mt-2 h-2 bg-gray-200 rounded-full">
                    <div id="gpu-progress" class="h-2 bg-purple-500 rounded-full" style="width: 0%"></div>
                </div>
            </div>

            <!-- GPU Memory Card -->
            <div class="bg-white p-4 rounded-lg shadow border">
                <h3 class="text-sm font-medium text-gray-500 mb-2">GPU Memory</h3>
                <div class="flex items-center">
                    <div class="text-2xl font-bold" id="gpu-memory-usage">--</div>
                    <div class="ml-2 text-sm text-gray-500">%</div>
                </div>
                <div class="mt-2 h-2 bg-gray-200 rounded-full">
                    <div id="gpu-memory-progress" class="h-2 bg-yellow-500 rounded-full" style="width: 0%"></div>
                </div>
                <div class="mt-1 text-xs text-gray-500" id="gpu-memory-details">-- / --</div>
            </div>
        </div>

        <div class="mt-6">
            <h3 class="font-semibold mb-4">Active Tests</h3>
            <div id="active-tests-container" class="bg-white rounded-lg shadow border overflow-hidden">
                <div id="no-tests-message" class="p-4 text-gray-500">No active tests</div>
                <div id="active-tests" class="hidden"></div>
            </div>
        </div>

        <div class="mt-6">
            <h3 class="font-semibold mb-4">Recent Metrics</h3>
            <div class="bg-white rounded-lg shadow border overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CPU</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Memory</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GPU</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GPU Mem</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CPU Temp</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GPU Temp</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tests</th>
                        </tr>
                    </thead>
                    <tbody id="metrics-table-body" class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">Loading metrics...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const serverId = {{ $server->id }};
        
        // Format bytes to human readable
        function formatBytes(bytes, decimals = 2) {
            if (bytes === 0) return '0 B';
            
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
            
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }
        
        // Update dashboard with latest metrics
        function updateDashboard(metrics) {
            if (!metrics || metrics.length === 0) {
                return;
            }
            
            // Sort metrics by collection time (newest first)
            metrics.sort((a, b) => new Date(b.collected_at) - new Date(a.collected_at));
            
            // Get latest metric for the dashboard cards
            const latestMetric = metrics[0];
            
            // Update CPU usage
            const cpuUsage = latestMetric.cpu_usage?.toFixed(1) || '--';
            document.getElementById('cpu-usage').textContent = cpuUsage;
            document.getElementById('cpu-progress').style.width = `${cpuUsage === '--' ? 0 : cpuUsage}%`;
            
            // Update memory usage
            const memoryPercent = latestMetric.memory_usage_percent?.toFixed(1) || '--';
            document.getElementById('memory-usage').textContent = memoryPercent;
            document.getElementById('memory-progress').style.width = `${memoryPercent === '--' ? 0 : memoryPercent}%`;
            
            if (latestMetric.memory_used && latestMetric.memory_total) {
                const usedFormatted = formatBytes(latestMetric.memory_used);
                const totalFormatted = formatBytes(latestMetric.memory_total);
                document.getElementById('memory-details').textContent = `${usedFormatted} / ${totalFormatted}`;
            } else {
                document.getElementById('memory-details').textContent = '-- / --';
            }
            
            // Update GPU usage
            const gpuUsage = latestMetric.gpu_utilization?.toFixed(1) || '--';
            document.getElementById('gpu-usage').textContent = gpuUsage;
            document.getElementById('gpu-progress').style.width = `${gpuUsage === '--' ? 0 : gpuUsage}%`;
            
            // Update GPU memory
            const gpuMemPercent = latestMetric.gpu_memory_utilization?.toFixed(1) || '--';
            document.getElementById('gpu-memory-usage').textContent = gpuMemPercent;
            document.getElementById('gpu-memory-progress').style.width = `${gpuMemPercent === '--' ? 0 : gpuMemPercent}%`;
            
            if (latestMetric.gpu_memory_used && latestMetric.gpu_memory_total) {
                const usedFormatted = formatBytes(latestMetric.gpu_memory_used);
                const totalFormatted = formatBytes(latestMetric.gpu_memory_total);
                document.getElementById('gpu-memory-details').textContent = `${usedFormatted} / ${totalFormatted}`;
            } else {
                document.getElementById('gpu-memory-details').textContent = '-- / --';
            }
            
            // Update active tests
            const activeTests = latestMetric.active_tests || [];
            const activeTestsContainer = document.getElementById('active-tests');
            const noTestsMessage = document.getElementById('no-tests-message');
            
            if (activeTests.length > 0) {
                activeTestsContainer.classList.remove('hidden');
                noTestsMessage.classList.add('hidden');
                
                // Create test elements if needed
                // In a real implementation, you would fetch actual test details
                activeTestsContainer.innerHTML = activeTests.map(testId => `
                    <div class="p-4 border-b last:border-b-0">
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="font-medium">Test #${testId}</span>
                                <div class="text-sm text-gray-500">In progress</div>
                            </div>
                            <div>
                                <a href="/ollama/servers/${serverId}/tests/${testId}" class="text-blue-500 hover:underline text-sm">View</a>
                            </div>
                        </div>
                    </div>
                `).join('');
            } else {
                activeTestsContainer.classList.add('hidden');
                noTestsMessage.classList.remove('hidden');
            }
            
            // Update metrics table (show up to 15 rows)
            const tableBody = document.getElementById('metrics-table-body');
            tableBody.innerHTML = metrics.slice(0, 15).map(metric => `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">${new Date(metric.collected_at).toLocaleString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">${metric.cpu_usage?.toFixed(1) || '--'}%</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">${metric.memory_usage_percent?.toFixed(1) || '--'}%</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">${metric.gpu_utilization?.toFixed(1) || '--'}%</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">${metric.gpu_memory_utilization?.toFixed(1) || '--'}%</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">${metric.cpu_temperature?.toFixed(1) || '--'}°C</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">${metric.gpu_temperature?.toFixed(1) || '--'}°C</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">${metric.active_tests?.length || 0}</td>
                </tr>
            `).join('');
        }
        
        // Fetch metrics from API
        function fetchMetrics() {
            const timeframe = document.getElementById('timeframeSelector').value;
            
            // Determine appropriate limit based on timeframe
            let limit = 30; // Default limit
            
            switch(timeframe) {
                case '15m':
                    limit = 30;
                    break;
                case '1h':
                    limit = 50;
                    break;
                case '6h':
                    limit = 75;
                    break;
                case '24h':
                    limit = 100;
                    break;
            }
            
            fetch(`/api/server-metrics/server/${serverId}?timeframe=${timeframe}&limit=${limit}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.metrics) {
                        updateDashboard(data.metrics);
                    }
                })
                .catch(error => {
                    console.error('Error fetching metrics:', error);
                    
                    // Show error message to user
                    const errorMsg = `
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 my-4">
                            <div class="flex">
                                <div class="ml-3">
                                    <p class="text-sm text-red-700">
                                        Error loading metrics data. Please refresh the page.
                                    </p>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Insert error message at the top of the page if not already there
                    if (!document.getElementById('metrics-error')) {
                        const container = document.querySelector('.p-6.text-gray-900');
                        const errorDiv = document.createElement('div');
                        errorDiv.id = 'metrics-error';
                        errorDiv.innerHTML = errorMsg;
                        container.insertBefore(errorDiv, container.firstChild);
                    }
                });
        }
        
        // Initialize the page
        fetchMetrics();
        
        // Set up auto-refresh every 10 seconds
        const refreshInterval = setInterval(fetchMetrics, 10000);
        
        // Manual refresh button
        document.getElementById('refreshBtn').addEventListener('click', fetchMetrics);
        
        // Timeframe selector change event
        document.getElementById('timeframeSelector').addEventListener('change', fetchMetrics);
        
        // Clean up on page unload
        window.addEventListener('beforeunload', function() {
            clearInterval(refreshInterval);
        });
        
        // Copy Pusher details to clipboard
        document.getElementById('copyPusherDetailsBtn').addEventListener('click', function() {
            const pusherDetails = {
                app_key: '{{ config('broadcasting.connections.pusher.key') }}',
                cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
                channel: 'server.{{ $server->id }}',
                event: 'App\\Events\\TestStatusUpdated'
            };
            
            const detailsText = JSON.stringify(pusherDetails, null, 2);
            
            // Copy to clipboard
            navigator.clipboard.writeText(detailsText).then(function() {
                alert('Pusher details copied to clipboard');
            }, function() {
                // Create a textarea, append it to document, select & copy, then remove it
                const textarea = document.createElement('textarea');
                textarea.value = detailsText;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                alert('Pusher details copied to clipboard');
            });
        });
    });
</script>
@endsection 