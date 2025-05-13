@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center mb-6">
        <a href="{{ route('ollama.tests.index', $server) }}" class="text-blue-500 hover:underline mr-4">
            &larr; Back to Tests
        </a>
        <h1 class="text-2xl font-semibold text-gray-800">Test Multiple Models on {{ $server->name }}</h1>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden p-6">
        <form action="{{ route('ollama.tests.store-multiple', $server) }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Models</label>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($models as $model)
                    <div class="border rounded-lg p-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="model_names[]" value="{{ $model['name'] }}"
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                            <span class="ml-2">
                                <span class="block text-sm font-medium text-gray-800">{{ $model['name'] }}</span>
                                <span class="block text-xs text-gray-500">{{ number_format($model['size'] / 1024 / 1024 / 1024, 2) }} GB</span>
                            </span>
                        </label>
                    </div>
                    @empty
                    <div class="col-span-3">
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        No models found on this server. Please make sure the server is running and accessible at {{ $server->url }}.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforelse
                </div>
                <div class="mt-2">
                    <button type="button" id="selectAllModels" class="text-blue-500 hover:underline text-sm">Select All</button> |
                    <button type="button" id="deselectAllModels" class="text-blue-500 hover:underline text-sm">Deselect All</button>
                </div>
                @error('model_names')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="prompt" class="block text-sm font-medium text-gray-700 mb-1">Prompt</label>
                <textarea name="prompt" id="prompt" rows="4" 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 text-gray-700"
                    required>{{ old('prompt') }}</textarea>
                <p class="text-gray-500 text-sm mt-1">Enter the prompt you want to send to all selected models.</p>
                @error('prompt')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="iterations" class="block text-sm font-medium text-gray-700 mb-1">Number of Iterations per Model</label>
                <input type="number" name="iterations" id="iterations" value="{{ old('iterations', 1) }}" min="1" max="10" 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 text-gray-700"
                    required>
                <p class="text-gray-500 text-sm mt-1">How many times to run the test for each model (1-10).</p>
                @error('iterations')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Run Mode</label>
                <div class="mt-2 flex gap-4">
                    <div class="flex items-center">
                        <input id="run_mode_sequential" name="run_mode" type="radio" value="sequential" class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600" checked>
                        <label for="run_mode_sequential" class="ml-2 block text-sm font-medium leading-6 text-gray-900">Sequential</label>
                        <div class="ml-2 text-xs text-gray-500">Run tests one after another</div>
                    </div>
                    <div class="flex items-center">
                        <input id="run_mode_parallel" name="run_mode" type="radio" value="parallel" class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600">
                        <label for="run_mode_parallel" class="ml-2 block text-sm font-medium leading-6 text-gray-900">Parallel</label>
                        <div class="ml-2 text-xs text-gray-500">Run tests simultaneously (stress test)</div>
                    </div>
                </div>
                @error('run_mode')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end">
                <a href="{{ route('ollama.tests.index', $server) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded mr-2">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">
                    Run Tests
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllBtn = document.getElementById('selectAllModels');
        const deselectAllBtn = document.getElementById('deselectAllModels');
        const modelCheckboxes = document.querySelectorAll('input[name="model_names[]"]');
        
        selectAllBtn.addEventListener('click', function() {
            modelCheckboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
        });
        
        deselectAllBtn.addEventListener('click', function() {
            modelCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
        });
    });
</script>
@endsection 