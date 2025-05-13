<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Ollama Tester') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>
<body class="font-sans antialiased bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="text-center">
            <h1 class="text-4xl font-bold mb-6 text-gray-900">Ollama Tester</h1>
            <p class="text-xl mb-8 text-gray-600">A comprehensive tool for testing and benchmarking Ollama models</p>
            
            <div class="flex flex-col md:flex-row items-center justify-center gap-4 mb-12">
                @auth
                    <a href="{{ route('ollama.servers.index') }}" 
                        class="px-5 py-3 bg-indigo-600 text-white rounded-lg shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" 
                        class="px-5 py-3 bg-indigo-600 text-white rounded-lg shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all">
                        Login
                    </a>
                    <a href="{{ route('register') }}" 
                        class="px-5 py-3 bg-white text-indigo-600 rounded-lg shadow-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all">
                        Register
                    </a>
                @endauth
            </div>
            
            <div class="bg-white p-8 rounded-xl shadow-lg">
                <h2 class="text-2xl font-semibold mb-4 text-gray-800">Features</h2>
                <div class="grid md:grid-cols-2 gap-6 text-left">
                    <div class="flex gap-3 items-start">
                        <div class="bg-indigo-100 p-2 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">Server Management</h3>
                            <p class="text-gray-600">Connect and manage multiple Ollama servers</p>
                        </div>
                    </div>
                    <div class="flex gap-3 items-start">
                        <div class="bg-indigo-100 p-2 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">Comprehensive Testing</h3>
                            <p class="text-gray-600">Test and compare model performance</p>
                        </div>
                    </div>
                    <div class="flex gap-3 items-start">
                        <div class="bg-indigo-100 p-2 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">Stress Testing</h3>
                            <p class="text-gray-600">Run parallel tests to evaluate server capacity</p>
                        </div>
                    </div>
                    <div class="flex gap-3 items-start">
                        <div class="bg-indigo-100 p-2 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">Real-time Monitoring</h3>
                            <p class="text-gray-600">Watch test results update in real-time</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 