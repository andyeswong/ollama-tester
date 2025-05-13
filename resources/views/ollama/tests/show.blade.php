@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center mb-6">
        <a href="{{ route('ollama.tests.index', $server) }}" class="text-blue-500 hover:underline mr-4">
            &larr; Back to Tests
        </a>
        <h1 class="text-2xl font-semibold text-gray-800">Test Result for {{ $test->model_name }}</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Test Information -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden p-6 md:col-span-1">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Test Information</h2>
            
            <div class="mb-3">
                <span class="block text-sm font-medium text-gray-500">Model</span>
                <span class="block text-gray-700">{{ $test->model_name }}</span>
            </div>
            
            <div class="py-3 sm:py-5">
                <dt class="text-sm font-medium text-gray-500">Status</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                        @if($test->status === 'completed') bg-green-100 text-green-800
                        @elseif($test->status === 'failed') bg-red-100 text-red-800
                        @elseif($test->status === 'in_progress') bg-blue-100 text-blue-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ ucfirst(str_replace('_', ' ', $test->status)) }}
                    </span>
                </dd>
            </div>
            
            <div class="mb-3">
                <span class="block text-sm font-medium text-gray-500">Response Time</span>
                <span class="block text-gray-700">{{ $test->response_time ? number_format($test->response_time, 2) . ' seconds' : 'N/A' }}</span>
            </div>
            
            <div class="mb-3">
                <span class="block text-sm font-medium text-gray-500">Created</span>
                <span class="block text-gray-700">{{ $test->created_at->format('M d, Y H:i:s') }}</span>
            </div>
            
            <div class="mb-3">
                <span class="block text-sm font-medium text-gray-500">Last Updated</span>
                <span class="block text-gray-700">{{ $test->updated_at->format('M d, Y H:i:s') }}</span>
            </div>

            @if(isset($test->metadata['run_mode']))
            <div class="py-3 sm:py-5">
                <dt class="text-sm font-medium text-gray-500">Run Mode</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                        @if($test->metadata['run_mode'] === 'sequential') bg-blue-100 text-blue-800
                        @else bg-purple-100 text-purple-800 @endif">
                        {{ ucfirst($test->metadata['run_mode']) }}
                        @if($test->metadata['run_mode'] === 'parallel')
                        (Stress Test)
                        @endif
                    </span>
                </dd>
            </div>
            @endif

            @if(isset($test->metadata['iteration']) && isset($test->metadata['total_iterations']))
            <div class="py-3 sm:py-5">
                <dt class="text-sm font-medium text-gray-500">Iteration</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0">
                    {{ $test->metadata['iteration'] }} of {{ $test->metadata['total_iterations'] }}
                </dd>
            </div>
            @endif

            @if($test->metadata)
            <div class="mt-6">
                <h3 class="text-lg font-medium mb-2 text-gray-800">Performance Metrics</h3>
                <div class="bg-gray-50 p-3 rounded">
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        @foreach($test->metadata as $key => $value)
                        <div class="col-span-1 font-medium text-gray-700">{{ Str::title(str_replace('_', ' ', $key)) }}</div>
                        <div class="col-span-1 text-gray-700">
                            @if(is_numeric($value))
                                {{ number_format($value, 4) }}
                                @if(Str::contains($key, 'duration'))
                                 s
                                @endif
                            @else
                                {{ $value }}
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
        
        <!-- Prompt and Response -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden p-6 md:col-span-2">
            <div class="mb-6">
                <h2 class="text-xl font-semibold mb-2 text-gray-800">Prompt</h2>
                <div class="bg-gray-50 p-4 rounded-lg whitespace-pre-wrap text-gray-700">{{ $test->prompt }}</div>
            </div>
            
            <div>
                <h2 class="text-xl font-semibold mb-2 text-gray-800">Response</h2>
                @if($test->status === 'completed')
                <div class="bg-gray-50 p-4 rounded-lg whitespace-pre-wrap text-gray-700">{{ $test->response }}</div>
                @elseif($test->status === 'failed')
                <div class="bg-red-50 p-4 rounded-lg text-red-700 whitespace-pre-wrap">{{ $test->response }}</div>
                @else
                <div class="bg-yellow-50 p-4 rounded-lg text-yellow-700">
                    Waiting for response...
                </div>
                @endif
            </div>

            @if($test->status === 'completed')
            <div class="mt-6">
                <h3 class="text-lg font-medium mb-2 text-gray-800">Run the same test again</h3>
                <form action="{{ route('ollama.tests.store', $server) }}" method="POST">
                    @csrf
                    <input type="hidden" name="model_name" value="{{ $test->model_name }}">
                    <input type="hidden" name="prompt" value="{{ $test->prompt }}">
                    <input type="hidden" name="iterations" value="1">
                    <input type="hidden" name="run_mode" value="{{ $test->metadata['run_mode'] ?? 'sequential' }}">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm">
                        Re-run Test
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection 