@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 animated-bg md:px-6">
    <div class="flex items-center mb-6">
        <a href="{{ route('ollama.servers.show', $server) }}" class="text-neon-primary hover:text-neon-primary/80 transition-colors mr-4">
            &larr; Back to {{ $server->name }}
        </a>
        <h1 class="text-2xl font-semibold neon-text-primary">Tests for {{ $server->name }}</h1>
    </div>

    @if(session('success'))
    <div class="glass border-l-4 border-neon-accent p-4 mb-6" role="alert">
        <p class="text-neon-accent">{{ session('success') }}</p>
    </div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <div>
            <form id="compareForm" action="{{ route('ollama.tests.compare', $server) }}" method="GET" class="inline">
                <!-- Hidden inputs for test_ids will be dynamically added by JavaScript -->
                <button type="submit" id="compareButton" class="neon-btn-secondary px-4 py-2 rounded opacity-50 cursor-not-allowed" disabled>
                    Compare Selected Tests
                </button>
            </form>
        </div>
        <div>
            <a href="{{ route('ollama.tests.create', $server) }}" class="neon-btn-primary px-4 py-2 rounded mr-2">
                New Test
            </a>
            <a href="{{ route('ollama.tests.create-multiple', $server) }}" class="neon-btn-accent px-4 py-2 rounded">
                Test Multiple Models
            </a>
        </div>
    </div>

    @php
        // Group tests by creation batch - use created_at timestamp to group tests that were created at the same time
        $batchedTests = $tests->sortByDesc('created_at')->groupBy(function($test) {
            // First try to group by test_group if available
            if (isset($test->metadata['test_group'])) {
                return $test->metadata['test_group'];
            }
            
            // If no test_group, group by similar creation time (within 1 second)
            // This helps group tests that were created together
            return $test->created_at->format('Y-m-d H:i');
        });
    @endphp

    @forelse($batchedTests as $batchId => $testsInBatch)
    <div class="glass-card mb-6">
        <div class="px-6 py-3 border-b border-glass-border flex justify-between items-center">
            <div>
                <h2 class="text-lg font-semibold neon-text-primary">
                    Test Group: {{ $testsInBatch->first()->model_name }}
                    <span class="text-sm font-normal text-foreground/70">
                        ({{ $testsInBatch->count() }} {{ Str::plural('test', $testsInBatch->count()) }} - {{ $testsInBatch->first()->created_at->format('M d, Y H:i') }})
                    </span>
                </h2>
            </div>
            <div>
                @if(isset($testsInBatch->first()->metadata['run_mode']))
                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full backdrop-blur-sm
                        @if($testsInBatch->first()->metadata['run_mode'] === 'sequential') 
                          bg-neon-primary/10 text-neon-primary border border-neon-primary/30
                        @else 
                          bg-neon-secondary/10 text-neon-secondary border border-neon-secondary/30 
                        @endif">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            @if($testsInBatch->first()->metadata['run_mode'] === 'sequential')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            @endif
                        </svg>
                        {{ ucfirst($testsInBatch->first()->metadata['run_mode']) }} Mode
                        @if($testsInBatch->first()->metadata['run_mode'] === 'parallel')
                        (Stress Test)
                        @endif
                    </span>
                @endif
            </div>
        </div>
        <table class="glass-table min-w-full divide-y divide-glass-border">
            <thead>
                <tr>
                    <th class="w-12 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                        <input type="checkbox" class="select-all-group rounded border-glass-border text-neon-primary shadow-sm focus:border-neon-primary focus:ring focus:ring-neon-primary/30 focus:ring-opacity-50" 
                               data-group="{{ $batchId }}">
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Model</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Prompt</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Response Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($testsInBatch as $test)
                <tr class="{{ $loop->even ? 'bg-glass-bg/20' : '' }} hover:bg-glass-bg/40 transition-colors duration-200" data-test-id="{{ $test->id }}">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="checkbox" name="test_ids[]" value="{{ $test->id }}" class="test-checkbox rounded border-glass-border text-neon-primary shadow-sm focus:border-neon-primary focus:ring focus:ring-neon-primary/30 focus:ring-opacity-50" data-group="{{ $batchId }}">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium">{{ $test->model_name }}</div>
                        @if(isset($test->metadata['iteration']) && isset($test->metadata['total_iterations']))
                        <div class="text-xs text-foreground/70">
                            Iteration {{ $test->metadata['iteration'] }} of {{ $test->metadata['total_iterations'] }}
                        </div>
                        @endif
                        @if(isset($test->metadata['run_mode']))
                        <div class="mt-1">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded backdrop-blur-sm
                                @if($test->metadata['run_mode'] === 'sequential') 
                                  bg-neon-primary/10 text-neon-primary
                                @else 
                                  bg-neon-secondary/10 text-neon-secondary
                                @endif">
                                @if($test->metadata['run_mode'] === 'sequential')
                                    Sequential
                                @else
                                    Parallel
                                @endif
                            </span>
                        </div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm">{{ Str::limit($test->prompt, 50) }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap test-status">
                        @if($test->status === 'completed')
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full backdrop-blur-sm bg-neon-accent/10 text-neon-accent border border-neon-accent/30">
                            Completed
                        </span>
                        @elseif($test->status === 'failed')
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full backdrop-blur-sm bg-destructive/10 text-destructive border border-destructive/30">
                            Failed
                        </span>
                        @elseif($test->status === 'in_progress')
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full backdrop-blur-sm bg-neon-primary/10 text-neon-primary border border-neon-primary/30">
                            In Progress
                        </span>
                        @else
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full backdrop-blur-sm bg-neon-secondary/10 text-neon-secondary border border-neon-secondary/30">
                            Pending
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground/70 response-time">
                        {{ $test->response_time ? number_format($test->response_time, 2) . 's' : '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground/70">
                        {{ $test->created_at->format('M d, Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-3">
                            <a href="{{ route('ollama.tests.show', ['server' => $server, 'test' => $test]) }}" class="text-neon-primary hover:text-neon-primary/80 transition-colors">View</a>
                            <form action="{{ route('ollama.tests.destroy', ['server' => $server, 'test' => $test]) }}" method="POST" class="inline"
                                onsubmit="return confirm('Are you sure you want to delete this test?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-destructive hover:text-destructive/80 transition-colors">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @empty
    <div class="glass-card p-6 text-center text-foreground/70">
        No tests found. <a href="{{ route('ollama.tests.create', $server) }}" class="text-neon-primary hover:text-neon-primary/80 transition-colors">Create one now</a>.
    </div>
    @endforelse
</div>

@endsection

@section('scripts')
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const testCheckboxes = document.querySelectorAll('.test-checkbox');
        const compareButton = document.getElementById('compareButton');
        const compareForm = document.getElementById('compareForm');
        const selectAllGroups = document.querySelectorAll('.select-all-group');
        
        function updateCompareButtonState() {
            const selectedCount = document.querySelectorAll('.test-checkbox:checked').length;
            
            if (selectedCount >= 2) {
                compareButton.classList.remove('opacity-50', 'cursor-not-allowed');
                compareButton.disabled = false;
            } else {
                compareButton.classList.add('opacity-50', 'cursor-not-allowed');
                compareButton.disabled = true;
            }
        }
        
        // Group-level select all
        selectAllGroups.forEach(groupSelectAll => {
            groupSelectAll.addEventListener('change', function() {
                const groupId = this.dataset.group;
                const groupCheckboxes = document.querySelectorAll(`.test-checkbox[data-group="${groupId}"]`);
                
                groupCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                
                updateCompareButtonState();
            });
        });
        
        testCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateCompareButtonState();
                
                // Update group select-all state
                const groupId = this.dataset.group;
                const groupCheckboxes = document.querySelectorAll(`.test-checkbox[data-group="${groupId}"]`);
                const groupSelectAll = document.querySelector(`.select-all-group[data-group="${groupId}"]`);
                
                if (groupSelectAll) {
                    const allChecked = Array.from(groupCheckboxes).every(cb => cb.checked);
                    groupSelectAll.checked = allChecked;
                }
            });
        });
        
        // Update compare form before submission to include selected test IDs
        compareForm.addEventListener('submit', function(e) {
            // First clear any existing hidden fields
            const existingFields = compareForm.querySelectorAll('input[name="test_ids[]"]');
            existingFields.forEach(field => field.remove());
            
            // Then add new hidden fields for each checked checkbox
            const selectedCheckboxes = document.querySelectorAll('.test-checkbox:checked');
            selectedCheckboxes.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'test_ids[]';
                input.value = checkbox.value;
                compareForm.appendChild(input);
            });
            
            // Only submit if at least 2 tests are selected
            if (selectedCheckboxes.length < 2) {
                e.preventDefault();
                alert('Please select at least 2 tests to compare.');
            }
        });
        
        // Initial button state
        updateCompareButtonState();
        
        // Real-time updates with Pusher
        try {
            // Only enable Pusher if it's configured
            if (typeof Pusher !== 'undefined') {
                // Set up Pusher
                const pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
                    cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
                    encrypted: true
                });
                
                // Subscribe to server channel
                const channel = pusher.subscribe('server.{{ $server->id }}');
                
                // Listen for test status updates
                channel.bind('App\\Events\\TestStatusUpdated', function(data) {
                    // Find the test row to update
                    const testRow = document.querySelector(`tr[data-test-id="${data.id}"]`);
                    if (testRow) {
                        // Update status badge
                        const statusCell = testRow.querySelector('.test-status');
                        if (statusCell) {
                            let newStatusHtml = '';
                            if (data.status === 'completed') {
                                newStatusHtml = `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-neon-accent/10 text-neon-accent border border-neon-accent/30">Completed</span>`;
                            } else if (data.status === 'failed') {
                                newStatusHtml = `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-destructive/10 text-destructive border border-destructive/30">Failed</span>`;
                            } else if (data.status === 'in_progress') {
                                newStatusHtml = `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-neon-primary/10 text-neon-primary border border-neon-primary/30">In Progress</span>`;
                            } else {
                                newStatusHtml = `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-neon-secondary/10 text-neon-secondary border border-neon-secondary/30">Pending</span>`;
                            }
                            statusCell.innerHTML = newStatusHtml;
                        }
                        
                        // Update response time
                        const timeCell = testRow.querySelector('.response-time');
                        if (timeCell && data.response_time) {
                            timeCell.textContent = (data.response_time).toFixed(2) + 's';
                        }
                    }
                });
                
                console.log('Real-time updates enabled');
            }
        } catch (error) {
            console.error('Error setting up real-time updates:', error);
        }
    });
</script>
@endsection 