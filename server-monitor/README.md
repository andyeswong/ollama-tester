# Ollama Server Monitor

This Node.js application monitors server metrics during Ollama model tests and reports them back to your Ollama Testing Lab application.

## Features

- Real-time monitoring of system resources (CPU, Memory, Temperature)
- NVIDIA GPU metrics collection (utilization, memory usage, temperature)
- Automatic detection of active tests using Pusher events
- REST API for manual control and status checks

## Requirements

- Node.js 14+
- npm
- NVIDIA GPU drivers (for GPU metrics)

## Installation

1. Clone this repository or copy the files to your Ollama server
2. Install dependencies:
   ```
   npm install
   ```
3. Configure the application by creating a `.env` file (see Configuration section)
4. Start the monitor:
   ```
   node index.js
   ```

## Configuration

Create a `.env` file with the following variables:

```
# API Configuration
API_URL=http://your-ollama-lab-url/api/server-metrics

# Pusher Configuration
PUSHER_APP_KEY=your-pusher-app-key
PUSHER_APP_CLUSTER=your-pusher-cluster

# Monitoring Configuration
MONITOR_INTERVAL=5000
NVIDIA_SMI_PATH=/usr/bin/nvidia-smi
SERVER_ID=1
PORT=3000
```

Adjust the values for your environment:
- `API_URL`: The URL of your Ollama Testing Lab API endpoint for server metrics
- `PUSHER_APP_KEY`: Your Pusher app key (from your Laravel .env file)
- `PUSHER_APP_CLUSTER`: Your Pusher cluster region (from your Laravel .env file)
- `MONITOR_INTERVAL`: How often to collect metrics (in milliseconds)
- `NVIDIA_SMI_PATH`: Path to the nvidia-smi executable
- `SERVER_ID`: The ID of the Ollama server in your database
- `PORT`: The port for the Express server

## REST API Endpoints

The monitor includes a simple REST API for checking status and manually controlling monitoring:

- `GET /status`: Get the current status of the monitor
- `GET /start`: Manually start monitoring
- `GET /stop`: Manually stop monitoring
- `GET /collect`: Force collection of metrics and return the results

## How It Works

1. The monitor listens for `TestStatusUpdated` events on the Pusher channel for the configured server ID
2. When a test enters the "in_progress" state, monitoring automatically starts
3. System metrics are collected at the configured interval and sent to your Laravel application
4. When all active tests complete, monitoring automatically stops

## Metrics Collected

### CPU Metrics
- Overall CPU usage percentage
- Per-core CPU usage

### Memory Metrics
- Total memory
- Used memory
- Memory usage percentage

### Temperature Metrics
- CPU temperature (if available)

### GPU Metrics (NVIDIA only)
- GPU utilization percentage
- GPU memory utilization percentage
- Total GPU memory
- Free GPU memory
- Used GPU memory
- GPU temperature 