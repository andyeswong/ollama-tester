<?php

namespace App\Http\Controllers;

use App\Models\OllamaServer;
use App\Services\OllamaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class OllamaServerController extends Controller
{
    protected OllamaService $ollamaService;

    public function __construct(OllamaService $ollamaService)
    {
        $this->ollamaService = $ollamaService;
    }

    /**
     * Display a listing of the servers.
     */
    public function index(): View
    {
        $servers = OllamaServer::latest()->get();
        
        return view('ollama.servers.index', compact('servers'));
    }

    /**
     * Show the form for creating a new server.
     */
    public function create(): View
    {
        return view('ollama.servers.create');
    }

    /**
     * Store a newly created server in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        OllamaServer::create($validated);

        return redirect()
            ->route('ollama.servers.index')
            ->with('success', 'Ollama server added successfully.');
    }

    /**
     * Display the specified server.
     */
    public function show(OllamaServer $server): View
    {
        $models = $this->ollamaService->getModels($server);
        
        return view('ollama.servers.show', compact('server', 'models'));
    }

    /**
     * Show the form for editing the specified server.
     */
    public function edit(OllamaServer $server): View
    {
        return view('ollama.servers.edit', compact('server'));
    }

    /**
     * Update the specified server in storage.
     */
    public function update(Request $request, OllamaServer $server): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $server->update($validated);

        return redirect()
            ->route('ollama.servers.index')
            ->with('success', 'Ollama server updated successfully.');
    }

    /**
     * Remove the specified server from storage.
     */
    public function destroy(OllamaServer $server): RedirectResponse
    {
        $server->delete();

        return redirect()
            ->route('ollama.servers.index')
            ->with('success', 'Ollama server deleted successfully.');
    }

    /**
     * Display the metrics for the specified server.
     */
    public function metrics(OllamaServer $server): View
    {
        return view('ollama.servers.metrics', compact('server'));
    }

    /**
     * Download a preconfigured server monitor agent for the specified server.
     */
    public function downloadAgent(OllamaServer $server)
    {
        // Create a temporary directory to prepare the files
        $tempDir = storage_path('app/temp/' . Str::random(10));
        File::makeDirectory($tempDir, 0755, true);
        
        // Create agent directory
        $agentDir = $tempDir . '/ollama-server-monitor';
        File::makeDirectory($agentDir, 0755, true);
        
        // Get the base URL of the app for the API endpoint
        $baseUrl = url('/');
        
        // Get the API prefix from config (this handles Laravel 12's flexible API routing)
        $apiPrefix = config('app.api_prefix', 'api');
        
        // Create .env file with server-specific configuration
        $envContent = <<<EOT
# API Configuration
API_URL={$baseUrl}/{$apiPrefix}/server-metrics

# Pusher Configuration
PUSHER_APP_KEY={$this->getPusherKey()}
PUSHER_APP_CLUSTER={$this->getPusherCluster()}

# Monitoring Configuration
MONITOR_INTERVAL=5000
NVIDIA_SMI_PATH=/usr/bin/nvidia-smi
SERVER_ID={$server->id}

# Debug Mode (set to true for verbose logging)
DEBUG=true

# Continuous Mode - Set to true if Pusher events aren't working
# This will make the agent collect metrics continuously regardless of test status
CONTINUOUS_MODE=false
EOT;
        
        File::put($agentDir . '/.env', $envContent);
        
        // Copy index.js, package.json, and README.md from the package
        $sourcePath = base_path('server-monitor');
        
        if (File::exists($sourcePath)) {
            File::copy($sourcePath . '/index.js', $agentDir . '/index.js');
            File::copy($sourcePath . '/package.json', $agentDir . '/package.json');
            
            // Create a customized README with installation instructions
            $readmeContent = <<<EOT
# Ollama Server Monitor for {$server->name}

This Node.js application monitors server metrics during Ollama model tests and reports them back to your Ollama Testing Lab application.

## Quick Setup

1. Extract this zip file on your Ollama server
2. Navigate to the extracted directory:
   ```
   cd ollama-server-monitor
   ```
3. Install dependencies:
   ```
   npm install
   ```
4. Start the monitor:
   ```
   node index.js
   ```

## Configuration

The .env file is already pre-configured for your server ({$server->name}) with ID: {$server->id}.

You may need to modify:
- NVIDIA_SMI_PATH if your nvidia-smi is located in a different path
- MONITOR_INTERVAL if you want to change how frequently metrics are collected (milliseconds)

## How It Works

1. The monitor listens for test events from your Ollama Testing Lab
2. When a test starts, monitoring automatically begins
3. Metrics are sent back to your application in real-time
4. When all tests finish, monitoring automatically stops

## Manual Control

If needed, you can manually control the monitor using:
- http://localhost:3000/start - Start monitoring
- http://localhost:3000/stop - Stop monitoring
- http://localhost:3000/status - Check current status

## Viewing Metrics

Once running, you can view metrics in your Ollama Testing Lab:
{$baseUrl}/ollama/servers/{$server->id}/metrics

EOT;
            File::put($agentDir . '/README.md', $readmeContent);
            
            // Create a setup instructions file
            $instructionsContent = <<<EOT
# Server Monitor Setup Instructions

Follow these steps to set up the Ollama Server Monitor on your server:

## Requirements

- Node.js 14 or higher
- npm
- NVIDIA drivers (if using GPU metrics)

## Installation

1. Extract this zip file on your Ollama server
2. Open a terminal and navigate to the extracted directory:
   ```
   cd ollama-server-monitor
   ```
3. Install the required dependencies:
   ```
   npm install
   ```
4. Start the monitor:
   ```
   node index.js
   ```

## Running as a Service

For production use, you may want to run the monitor as a system service:

### Using PM2
```
npm install -g pm2
pm2 start index.js --name "ollama-monitor"
pm2 save
pm2 startup
```

### Using Systemd
Create a file at /etc/systemd/system/ollama-monitor.service:

```
[Unit]
Description=Ollama Server Monitor
After=network.target

[Service]
WorkingDirectory=/path/to/ollama-server-monitor
ExecStart=/usr/bin/node index.js
Restart=always
User=your-username
Environment=NODE_ENV=production

[Install]
WantedBy=multi-user.target
```

Then enable and start the service:
```
sudo systemctl enable ollama-monitor
sudo systemctl start ollama-monitor
```

## Verification

To verify the monitor is running correctly, check:
1. The console output for any errors
2. Visit http://localhost:3000/status in a browser on the server
3. Check the metrics page in your Ollama Testing Lab

EOT;
            File::put($agentDir . '/SETUP-INSTRUCTIONS.md', $instructionsContent);
                        
            // Create the zip file
            $zipFileName = "ollama-server-monitor-{$server->id}.zip";
            $zipPath = storage_path('app/temp/' . $zipFileName);
            
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($agentDir),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                );
                
                foreach ($files as $file) {
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = 'ollama-server-monitor/' . substr($filePath, strlen($agentDir) + 1);
                        
                        $zip->addFile($filePath, $relativePath);
                    }
                }
                
                $zip->close();
                
                // Clean up the temporary directory
                File::deleteDirectory($tempDir);
                
                // Return the zip file as a download
                return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
            }
            
            // If zip creation failed
            File::deleteDirectory($tempDir);
            return back()->with('error', 'Failed to create agent download package.');
        }
        
        // If source files don't exist
        return back()->with('error', 'Server monitor files not found.');
    }
    
    /**
     * Get Pusher app key from environment
     */
    private function getPusherKey()
    {
        return config('broadcasting.connections.pusher.key', '2e823bfc618d890bccc3');
    }
    
    /**
     * Get Pusher cluster from environment
     */
    private function getPusherCluster()
    {
        return config('broadcasting.connections.pusher.options.cluster', 'us3');
    }
    
    /**
     * Testing endpoint for pusher events (diagnostic)
     */
    public function testPusherEvent(OllamaServer $server)
    {
        try {
            // Get Pusher configuration
            $pusherKey = $this->getPusherKey();
            $pusherCluster = $this->getPusherCluster();
            
            // Broadcast a test event
            event(new \App\Events\TestStatusUpdated(
                (object)[
                    'id' => 999,
                    'ollama_server_id' => $server->id,
                    'updated_at' => now(),
                    'response_time' => null,
                    'metadata' => []
                ],
                'diagnostic_test'
            ));
            
            return response()->json([
                'success' => true,
                'message' => 'Test event broadcasted to server.' . $server->id . ' channel',
                'config' => [
                    'pusher_key' => $pusherKey,
                    'pusher_cluster' => $pusherCluster,
                    'channel' => 'server.' . $server->id
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to broadcast test event',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
} 