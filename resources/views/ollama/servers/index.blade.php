@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 animated-bg md:px-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold neon-text-primary">Ollama Servers</h1>
        <a href="{{ route('ollama.servers.create') }}" class="neon-btn-primary px-4 py-2 rounded">
            Add Server
        </a>
    </div>

    @if(session('success'))
    <div class="glass border-l-4 border-neon-accent p-4 mb-6" role="alert">
        <p class="text-neon-accent">{{ session('success') }}</p>
    </div>
    @endif

    <div class="glass-card overflow-hidden">
        <table class="glass-table min-w-full divide-y divide-glass-border">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">URL</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($servers as $server)
                <tr class="hover:bg-glass-bg/40 transition-colors duration-200">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium">{{ $server->name }}</div>
                        @if($server->description)
                        <div class="text-sm text-foreground/70">{{ Str::limit($server->description, 50) }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground/70">
                        {{ $server->url }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($server->is_active)
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full backdrop-blur-sm bg-neon-accent/10 text-neon-accent border border-neon-accent/30">
                            Active
                        </span>
                        @else
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full backdrop-blur-sm bg-destructive/10 text-destructive border border-destructive/30">
                            Inactive
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-3">
                            <a href="{{ route('ollama.servers.show', $server) }}" class="text-neon-primary hover:text-neon-primary/80 transition-colors">View</a>
                            <a href="{{ route('ollama.servers.edit', $server) }}" class="text-neon-secondary hover:text-neon-secondary/80 transition-colors">Edit</a>
                            <a href="{{ route('ollama.tests.index', $server) }}" class="text-neon-accent hover:text-neon-accent/80 transition-colors">Tests</a>
                            <form action="{{ route('ollama.servers.destroy', $server) }}" method="POST" class="inline"
                                onsubmit="return confirm('Are you sure you want to delete this server?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-destructive hover:text-destructive/80 transition-colors">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-sm text-foreground/70">
                        No Ollama servers found. <a href="{{ route('ollama.servers.create') }}" class="text-neon-primary hover:text-neon-primary/80 transition-colors">Add one now</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection 