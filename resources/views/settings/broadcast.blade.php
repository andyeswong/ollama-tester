@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Broadcast Configuration</h1>
        <a href="{{ route('settings.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded">
            Back to Settings
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
        <p>{{ session('success') }}</p>
    </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b">
            <h2 class="text-xl font-semibold text-gray-800">Pusher Configuration</h2>
            <p class="text-sm text-gray-600 mt-1">Configure real-time updates using Pusher</p>
        </div>
        
        <form action="{{ route('settings.broadcast.update') }}" method="POST" class="p-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="mb-4">
                        <label for="broadcast_driver" class="block text-sm font-medium text-gray-700 mb-1">Broadcast Driver</label>
                        <select name="broadcast_driver" id="broadcast_driver" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                            <option value="pusher" {{ env('BROADCAST_DRIVER') === 'pusher' ? 'selected' : '' }}>Pusher</option>
                            <option value="redis" {{ env('BROADCAST_DRIVER') === 'redis' ? 'selected' : '' }}>Redis</option>
                            <option value="log" {{ env('BROADCAST_DRIVER') === 'log' ? 'selected' : '' }}>Log</option>
                            <option value="null" {{ env('BROADCAST_DRIVER') === 'null' ? 'selected' : '' }}>Null</option>
                        </select>
                        <p class="text-gray-500 text-xs mt-1">Select the broadcast driver to use for real-time updates.</p>
                    </div>
                    
                    <div class="mb-4">
                        <label for="pusher_app_id" class="block text-sm font-medium text-gray-700 mb-1">Pusher App ID</label>
                        <input type="text" name="pusher_app_id" id="pusher_app_id" value="{{ env('PUSHER_APP_ID') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    </div>
                    
                    <div class="mb-4">
                        <label for="pusher_app_key" class="block text-sm font-medium text-gray-700 mb-1">Pusher App Key</label>
                        <input type="text" name="pusher_app_key" id="pusher_app_key" value="{{ env('PUSHER_APP_KEY') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    </div>
                    
                    <div class="mb-4">
                        <label for="pusher_app_secret" class="block text-sm font-medium text-gray-700 mb-1">Pusher App Secret</label>
                        <input type="password" name="pusher_app_secret" id="pusher_app_secret" value="{{ env('PUSHER_APP_SECRET') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    </div>
                </div>
                
                <div>
                    <div class="mb-4">
                        <label for="pusher_app_cluster" class="block text-sm font-medium text-gray-700 mb-1">Pusher App Cluster</label>
                        <input type="text" name="pusher_app_cluster" id="pusher_app_cluster" value="{{ env('PUSHER_APP_CLUSTER', 'mt1') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                        <p class="text-gray-500 text-xs mt-1">Common clusters: us2, eu, ap1, ap2, etc.</p>
                    </div>
                    
                    <div class="mb-4">
                        <label for="pusher_host" class="block text-sm font-medium text-gray-700 mb-1">Pusher Host (optional)</label>
                        <input type="text" name="pusher_host" id="pusher_host" value="{{ env('PUSHER_HOST') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                        <p class="text-gray-500 text-xs mt-1">Only needed for self-hosted Pusher servers.</p>
                    </div>
                    
                    <div class="mb-4">
                        <label for="pusher_port" class="block text-sm font-medium text-gray-700 mb-1">Pusher Port</label>
                        <input type="text" name="pusher_port" id="pusher_port" value="{{ env('PUSHER_PORT', '443') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    </div>
                    
                    <div class="mb-4">
                        <label for="pusher_scheme" class="block text-sm font-medium text-gray-700 mb-1">Pusher Scheme</label>
                        <select name="pusher_scheme" id="pusher_scheme" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                            <option value="https" {{ env('PUSHER_SCHEME') === 'https' ? 'selected' : '' }}>HTTPS</option>
                            <option value="http" {{ env('PUSHER_SCHEME') === 'http' ? 'selected' : '' }}>HTTP</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 bg-gray-50 p-4 rounded border">
                <h3 class="font-medium text-gray-700 mb-2">Testing Connection</h3>
                <p class="text-sm text-gray-600 mb-2">After saving your Pusher configuration, you can test the connection to ensure real-time updates are working.</p>
                <button type="button" id="testConnection" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded text-sm">
                    Test Connection
                </button>
                <div id="connectionStatus" class="mt-2 text-sm"></div>
            </div>
            
            <div class="mt-6 flex justify-end">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Save Configuration
                </button>
            </div>
        </form>
    </div>
    
    <div class="mt-6 bg-white shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b">
            <h2 class="text-xl font-semibold text-gray-800">About Real-time Updates</h2>
        </div>
        <div class="p-6">
            <p class="text-gray-700 mb-4">
                Real-time updates allow you to see test results as they happen without refreshing the page. This is particularly useful when running multiple tests or stress tests.
            </p>
            
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                <div class="flex">
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>How to get Pusher credentials:</strong> 
                            <a href="https://pusher.com/channels" target="_blank" class="underline">Sign up for a free Pusher account</a> and create a new Channels app to get your app ID, key, secret, and cluster.
                        </p>
                    </div>
                </div>
            </div>
            
            <h3 class="font-medium text-gray-800 mb-2">Features enabled by real-time updates:</h3>
            <ul class="list-disc pl-5 text-gray-700 space-y-1">
                <li>See test status changes instantly</li>
                <li>Get real-time response times as tests complete</li>
                <li>Monitor parallel stress tests as they run</li>
                <li>Receive notifications when tests fail</li>
            </ul>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const testButton = document.getElementById('testConnection');
        const connectionStatus = document.getElementById('connectionStatus');
        
        testButton.addEventListener('click', function() {
            connectionStatus.innerHTML = '<span class="text-blue-600">Testing connection...</span>';
            
            const appId = document.getElementById('pusher_app_id').value;
            const appKey = document.getElementById('pusher_app_key').value;
            const appSecret = document.getElementById('pusher_app_secret').value;
            const appCluster = document.getElementById('pusher_app_cluster').value;
            
            if (!appId || !appKey || !appSecret || !appCluster) {
                connectionStatus.innerHTML = '<span class="text-red-600">Please fill in all required Pusher fields.</span>';
                return;
            }
            
            try {
                const pusher = new Pusher(appKey, {
                    cluster: appCluster,
                    encrypted: true
                });
                
                pusher.connection.bind('connected', function() {
                    connectionStatus.innerHTML = '<span class="text-green-600">✓ Connection successful! Pusher is properly configured.</span>';
                    setTimeout(() => {
                        pusher.disconnect();
                    }, 2000);
                });
                
                pusher.connection.bind('failed', function() {
                    connectionStatus.innerHTML = '<span class="text-red-600">✗ Connection failed. Please check your Pusher credentials.</span>';
                });
                
            } catch (error) {
                connectionStatus.innerHTML = `<span class="text-red-600">✗ Error: ${error.message}</span>`;
            }
        });
    });
</script>
@endsection 