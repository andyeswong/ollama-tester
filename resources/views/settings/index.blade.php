@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Settings</h1>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
        <p>{{ session('success') }}</p>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h2 class="text-xl font-semibold text-gray-800">Real-time Updates</h2>
                <p class="text-sm text-gray-600 mt-1">Configure Pusher for real-time test updates</p>
            </div>
            <div class="p-6">
                <p class="text-gray-700 mb-4">
                    Enable real-time updates to see test results instantly without refreshing the page.
                    This is particularly useful for monitoring stress tests.
                </p>
                <div class="mt-4">
                    <a href="{{ route('settings.broadcast') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                        Configure Real-time Updates
                    </a>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h2 class="text-xl font-semibold text-gray-800">Application Settings</h2>
                <p class="text-sm text-gray-600 mt-1">General application configuration</p>
            </div>
            <div class="p-6">
                <p class="text-gray-700 mb-4">
                    Configure general application settings like default server URL and other options.
                </p>
                <div class="mt-4">
                    <a href="#" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded cursor-not-allowed opacity-50">
                        Coming Soon
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 