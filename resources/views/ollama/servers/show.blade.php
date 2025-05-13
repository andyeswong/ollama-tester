@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 animated-bg md:px-6">
    <div class="flex items-center mb-6">
        <a href="{{ route('ollama.servers.index') }}" class="text-neon-primary hover:text-neon-primary/80 transition-colors mr-4">
            &larr; Back to Servers
        </a>
        <h1 class="text-2xl font-semibold neon-text-primary">{{ $server->name }}</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Server Information -->
        <div class="glass-card p-6 md:col-span-1">
            <h2 class="text-xl font-semibold mb-4 neon-text-primary">Server Information</h2>
            
            <div class="mb-3">
                <span class="block text-sm font-medium text-foreground/70">URL</span>
                <span class="block">{{ $server->url }}</span>
            </div>
            
            @if($server->description)
            <div class="mb-3">
                <span class="block text-sm font-medium text-foreground/70">Description</span>
                <span class="block">{{ $server->description }}</span>
            </div>
            @endif
            
            <div class="mb-3">
                <span class="block text-sm font-medium text-foreground/70">Status</span>
                <span class="block">
                    @if($server->is_active)
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full backdrop-blur-sm bg-neon-accent/10 text-neon-accent border border-neon-accent/30">
                        Active
                    </span>
                    @else
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full backdrop-blur-sm bg-destructive/10 text-destructive border border-destructive/30">
                        Inactive
                    </span>
                    @endif
                </span>
            </div>
            
            <div class="mt-6 flex flex-wrap gap-2">
                <a href="{{ route('ollama.servers.edit', $server) }}" class="neon-btn-primary px-4 py-2 rounded text-sm">
                    Edit Server
                </a>
                <a href="{{ route('ollama.tests.index', $server) }}" class="neon-btn-accent px-4 py-2 rounded text-sm">
                    View Tests
                </a>
                <a href="{{ route('ollama.servers.metrics', $server) }}" class="neon-btn-secondary px-4 py-2 rounded text-sm">
                    View Metrics
                </a>
            </div>
            
            <div class="mt-4 border-t border-glass-border pt-4">
                <h3 class="text-lg font-medium mb-2 neon-text-primary">Server Monitor</h3>
                <p class="text-sm text-foreground/70 mb-3">Download the server monitor agent to track resource usage during tests.</p>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('ollama.servers.download-agent', $server) }}" class="neon-btn-primary px-4 py-2 rounded text-sm inline-flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Download Monitor Agent
                    </a>
                    <button 
                        type="button" 
                        class="glass px-4 py-2 rounded text-sm inline-flex items-center hover:bg-glass-bg/50 transition-colors"
                        onclick="document.getElementById('setup-modal').classList.remove('hidden')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Installation Guide
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Available Models -->
        <div class="glass-card p-6 md:col-span-2">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold neon-text-primary">Available Models</h2>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('ollama.tests.create', $server) }}" class="neon-btn-primary px-4 py-2 rounded text-sm">
                        Run Single Test
                    </a>
                    <a href="{{ route('ollama.tests.create-multiple', $server) }}" class="neon-btn-accent px-4 py-2 rounded text-sm">
                        Test Multiple Models
                    </a>
                </div>
            </div>
            
            @if(count($models) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($models as $model)
                <div class="glass border rounded-lg p-4 flex flex-col hover:bg-glass-bg/50 transition-colors">
                    <div class="font-medium text-lg mb-1 neon-text-primary">{{ $model['name'] }}</div>
                    <div class="text-sm text-foreground/70 mb-2">{{ number_format($model['size'] / 1024 / 1024 / 1024, 2) }} GB</div>
                    <div class="text-xs text-foreground/60 mb-3">{{ $model['modified_at'] }}</div>
                    
                    <div class="mt-auto">
                        <a href="{{ route('ollama.tests.create', ['server' => $server, 'model' => $model['name']]) }}" 
                            class="text-neon-primary hover:text-neon-primary/80 transition-colors text-sm">
                            Test this model
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="glass border-l-4 border-neon-secondary p-4">
                <div class="flex">
                    <div class="ml-3">
                        <p class="text-sm">
                            No models found on this server. Please make sure the server is running and accessible at {{ $server->url }}.
                        </p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Server Monitor Setup Modal -->
<div id="setup-modal" class="fixed inset-0 bg-glass-bg/70 backdrop-blur-md flex items-center justify-center z-50 hidden">
    <div class="glass-card max-w-3xl w-full max-h-screen overflow-y-auto m-4">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold neon-text-primary">Server Monitor Setup Guide</h3>
                <button type="button" class="text-foreground/70 hover:text-foreground transition-colors" onclick="document.getElementById('setup-modal').classList.add('hidden')">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <div class="prose max-w-none">
                <h4 class="text-neon-secondary">Step 1: Download the Monitor Agent</h4>
                <p>Click the "Download Monitor Agent" button to get a ZIP file with all necessary files pre-configured for this server.</p>
                
                <h4 class="text-neon-secondary">Step 2: Extract and Install on Your Ollama Server</h4>
                <pre class="bg-glass-bg/30 p-3 rounded"><code>unzip ollama-server-monitor-{{ $server->id }}.zip
cd ollama-server-monitor
npm install</code></pre>
                
                <h4 class="text-neon-secondary">Step 3: Start the Monitor</h4>
                <pre class="bg-glass-bg/30 p-3 rounded"><code>node index.js</code></pre>
                <p>You should see output indicating the monitor is running and connected to the Pusher channel.</p>
                
                <h4 class="text-neon-secondary">Step 4: Verify Setup</h4>
                <p>After starting the monitor, you can verify it's working by:</p>
                <ul>
                    <li>Visiting <code class="bg-glass-bg/30 px-1 rounded">http://your-server:3000/status</code> to check the monitor status</li>
                    <li>Running a test on this server and observing metrics appear on the metrics page</li>
                </ul>
                
                <h4 class="text-neon-secondary">Troubleshooting</h4>
                <p>If you encounter issues:</p>
                <ul>
                    <li><strong>Windows users:</strong> If you see an error like <code class="bg-glass-bg/30 px-1 rounded">"E:\cmder\vendor\init.bat" is not recognized</code>, ignore this warning as it's related to your terminal configuration and not the monitor.</li>
                    <li><strong>API 404 errors:</strong> Make sure your Ollama Testing Lab application is accessible from the server. You may need to adjust the API_URL in the .env file if you're using a custom domain or port.</li>
                    <li><strong>Connection issues:</strong> Check that your Pusher credentials are correct and that the server can reach the Pusher service.</li>
                    <li><strong>Pusher events not working:</strong> If the monitor isn't starting automatically when tests run, edit the .env file and set <code class="bg-glass-bg/30 px-1 rounded">CONTINUOUS_MODE=true</code>. This will make the agent collect metrics continuously regardless of test status.</li>
                    <li><strong>Verify API requests:</strong> The agent now logs every API request with detailed information. Watch the console output to verify requests are being made and check their status.</li>
                    <li><strong>Debug mode:</strong> Debug mode is enabled by default (DEBUG=true in .env). This provides verbose logging of all metrics collection cycles and API communications.</li>
                </ul>
                
                <h4 class="text-neon-secondary">Running as a Background Service</h4>
                <p>For production use, you'll want to run the monitor as a persistent service:</p>
                
                <h5 class="text-neon-accent">Using PM2 (Recommended)</h5>
                <pre class="bg-glass-bg/30 p-3 rounded"><code>npm install -g pm2
pm2 start index.js --name "ollama-monitor"
pm2 save
pm2 startup</code></pre>
                
                <h5 class="text-neon-accent">Using Systemd</h5>
                <p>Create a service file at <code class="bg-glass-bg/30 px-1 rounded">/etc/systemd/system/ollama-monitor.service</code>:</p>
                <pre class="bg-glass-bg/30 p-3 rounded"><code>[Unit]
Description=Ollama Server Monitor
After=network.target

[Service]
WorkingDirectory=/path/to/ollama-server-monitor
ExecStart=/usr/bin/node index.js
Restart=always
User=your-username
Environment=NODE_ENV=production

[Install]
WantedBy=multi-user.target</code></pre>
                
                <p>Then enable and start the service:</p>
                <pre class="bg-glass-bg/30 p-3 rounded"><code>sudo systemctl enable ollama-monitor
sudo systemctl start ollama-monitor</code></pre>
                
                <h4 class="text-neon-secondary">More Information</h4>
                <p>For more details, check the README.md and SETUP-INSTRUCTIONS.md files included in the download.</p>
            </div>
        </div>
    </div>
</div>

@endsection 