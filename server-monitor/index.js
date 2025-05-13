require('dotenv').config();
const axios = require('axios');
const Pusher = require('pusher-js');
const cron = require('node-cron');
const si = require('systeminformation');
const { exec } = require('child_process');
const express = require('express');
const util = require('util');
const execPromise = util.promisify(exec);

// Configuration
const API_URL = process.env.API_URL;
const PUSHER_APP_KEY = process.env.PUSHER_APP_KEY;
const PUSHER_APP_CLUSTER = process.env.PUSHER_APP_CLUSTER;
const MONITOR_INTERVAL = parseInt(process.env.MONITOR_INTERVAL) || 5000;
const NVIDIA_SMI_PATH = process.env.NVIDIA_SMI_PATH || '/usr/bin/nvidia-smi';
const SERVER_ID = process.env.SERVER_ID || 1;
const DEBUG = process.env.DEBUG === 'true' || process.env.DEBUG === '1' || false;
const CONTINUOUS_MODE = process.env.CONTINUOUS_MODE === 'true' || process.env.CONTINUOUS_MODE === '1' || false;

// Express app for status endpoint
const app = express();
const PORT = process.env.PORT || 3000;

// Global variables to track active tests
let activeTests = new Set();
let isMonitoring = false;
let lastMetrics = null;
let monitoringInterval = null;

// Initialize Pusher client
const pusher = new Pusher(PUSHER_APP_KEY, {
    cluster: PUSHER_APP_CLUSTER,
    encrypted: true
});

// Subscribe to server channel
const channel = pusher.subscribe(`server.${SERVER_ID}`);

// Listen for test status updates
channel.bind('App\\Events\\TestStatusUpdated', function(data) {
    console.log('Received test update:', data);
    
    if (data.status === 'in_progress') {
        // Add test to active tests
        activeTests.add(data.id);
        // Start monitoring if not already
        startMonitoring();
    } else if (data.status === 'completed' || data.status === 'failed') {
        // Remove test from active tests
        activeTests.delete(data.id);
        
        // If no more active tests, stop monitoring
        if (activeTests.size === 0) {
            stopMonitoring();
        }
    }
});

// Function to get NVIDIA GPU metrics
async function getNvidiaMetrics() {
    try {
        const { stdout } = await execPromise(`${NVIDIA_SMI_PATH} --query-gpu=utilization.gpu,utilization.memory,memory.total,memory.free,memory.used,temperature.gpu --format=csv,noheader,nounits`);
        
        const metrics = stdout.trim().split(',').map(value => parseFloat(value.trim()));
        
        if (metrics.length >= 6) {
            return {
                gpu_utilization: metrics[0],
                memory_utilization: metrics[1],
                memory_total: metrics[2],
                memory_free: metrics[3],
                memory_used: metrics[4],
                temperature: metrics[5]
            };
        }
        
        return null;
    } catch (error) {
        console.error('Error getting NVIDIA metrics:', error.message);
        return null;
    }
}

// Function to collect system metrics
async function collectMetrics() {
    try {
        // Get CPU metrics
        const cpu = await si.currentLoad();
        const mem = await si.mem();
        const temp = await si.cpuTemperature();
        
        // Get NVIDIA GPU metrics if available
        let gpuMetrics = null;
        try {
            gpuMetrics = await getNvidiaMetrics();
        } catch (error) {
            console.log('NVIDIA GPU metrics not available:', error.message);
        }
        
        const metrics = {
            timestamp: new Date(),
            server_id: SERVER_ID,
            active_tests: Array.from(activeTests),
            cpu: {
                usage: cpu.currentLoad,
                cores: cpu.cpus.map(core => core.load)
            },
            memory: {
                total: mem.total,
                used: mem.used,
                usage_percent: (mem.used / mem.total) * 100
            },
            temperatures: {
                cpu: temp.main || null
            },
            gpu: gpuMetrics
        };
        
        lastMetrics = metrics;
        return metrics;
    } catch (error) {
        console.error('Error collecting metrics:', error.message);
        return null;
    }
}

// Function to send metrics to API
async function sendMetrics(metrics) {
    try {
        if (!metrics) return;
        
        const requestTime = new Date().toISOString();
        console.log(`\n[${requestTime}] PREPARING API REQUEST`);
        console.log('--------------------------------------------------');
        console.log(`URL: ${API_URL}`);
        console.log(`Method: POST`);
        console.log(`Server ID: ${SERVER_ID}`);
        console.log(`Active tests: ${Array.from(activeTests).join(', ') || 'None'}`);
        console.log('Metrics summary:');
        console.log(`  - CPU: ${metrics.cpu?.usage?.toFixed(2)}%`);
        console.log(`  - Memory: ${metrics.memory?.usage_percent?.toFixed(2)}%`);
        if (metrics.gpu) {
            console.log(`  - GPU: ${metrics.gpu?.gpu_utilization?.toFixed(2)}%`);
            console.log(`  - GPU Memory: ${metrics.gpu?.memory_utilization?.toFixed(2)}%`);
        }
        console.log('--------------------------------------------------');
        
        console.log('Sending request...');
        const startTime = Date.now();
        
        try {
            const response = await axios.post(API_URL, metrics);
            const endTime = Date.now();
            const duration = endTime - startTime;
            
            console.log(`\n[${new Date().toISOString()}] API REQUEST SUCCESSFUL`);
            console.log('--------------------------------------------------');
            console.log(`Status: ${response.status} ${response.statusText}`);
            console.log(`Duration: ${duration}ms`);
            console.log(`Response:`, response.data);
            console.log('--------------------------------------------------');
        } catch (error) {
            const endTime = Date.now();
            const duration = endTime - startTime;
            
            console.log(`\n[${new Date().toISOString()}] API REQUEST FAILED`);
            console.log('--------------------------------------------------');
            console.log(`Duration: ${duration}ms`);
            console.log(`Error status: ${error.response?.status || 'Unknown'}`);
            console.log(`Error message: ${error.message}`);
            
            if (error.response) {
                console.log(`Response data:`, error.response.data);
                console.log(`Response headers:`, error.response.headers);
            } else if (error.request) {
                console.log('No response received. Request details:', error.request._currentUrl);
            }
            console.log('--------------------------------------------------');
            console.log('Full error:', error);
        }
    } catch (rootError) {
        console.error('Fatal error in sendMetrics function:', rootError);
    }
}

// Function to start monitoring
function startMonitoring() {
    if (isMonitoring) return;
    
    console.log('\n==================================================');
    console.log(`STARTING SERVER MONITORING (${new Date().toISOString()})`);
    console.log(`Interval: ${MONITOR_INTERVAL}ms (${MONITOR_INTERVAL/1000} seconds)`);
    console.log(`Debug mode: ${DEBUG ? 'Enabled' : 'Disabled'}`);
    console.log('==================================================\n');
    
    isMonitoring = true;
    let cycleCount = 0;
    
    // Collect and send metrics immediately
    collectMetrics().then((metrics) => {
        cycleCount++;
        if (DEBUG) console.log(`\n[MONITOR CYCLE #${cycleCount}] Collecting initial metrics...`);
        sendMetrics(metrics);
    });
    
    // Set up interval for continuous monitoring
    monitoringInterval = setInterval(async () => {
        cycleCount++;
        if (DEBUG) {
            console.log(`\n[MONITOR CYCLE #${cycleCount}] Collecting metrics at ${new Date().toISOString()}...`);
            console.log(`Active tests: ${Array.from(activeTests).join(', ') || 'None'}`);
        }
        const metrics = await collectMetrics();
        await sendMetrics(metrics);
    }, MONITOR_INTERVAL);
}

// Function to stop monitoring
function stopMonitoring() {
    if (!isMonitoring) return;
    
    // If in continuous mode and it's the only "active test", don't stop monitoring
    if (CONTINUOUS_MODE && activeTests.size === 1 && activeTests.has('continuous-mode')) {
        console.log('\n==================================================');
        console.log(`CONTINUOUS MODE ACTIVE - IGNORING STOP REQUEST (${new Date().toISOString()})`);
        console.log('==================================================\n');
        return;
    }
    
    console.log('\n==================================================');
    console.log(`STOPPING SERVER MONITORING (${new Date().toISOString()})`);
    console.log('Reason: No active tests remaining');
    console.log('==================================================\n');
    
    isMonitoring = false;
    
    // Clear the monitoring interval
    if (monitoringInterval) {
        clearInterval(monitoringInterval);
        monitoringInterval = null;
    }
    
    // Send one final set of metrics
    console.log('Sending final metrics before stopping...');
    collectMetrics().then(sendMetrics);
}

// Status endpoint for checking the monitor
app.get('/status', (req, res) => {
    res.json({
        status: 'running',
        monitoring: isMonitoring,
        continuous_mode: CONTINUOUS_MODE,
        activeTests: Array.from(activeTests),
        lastMetrics
    });
});

// Debug Pusher connection
app.get('/debug-pusher', (req, res) => {
    const pusherInfo = {
        connected: pusher.connection.state === 'connected',
        connectionState: pusher.connection.state,
        config: {
            key: PUSHER_APP_KEY,
            cluster: PUSHER_APP_CLUSTER,
            channel: `server.${SERVER_ID}`,
            expectedEventName: 'App\\Events\\TestStatusUpdated'
        },
        activeSubscriptions: Object.keys(pusher.channels.channels).map(channelName => ({
            channelName,
            state: pusher.channels.channels[channelName].subscriptionPending ? 'pending' : 'subscribed'
        }))
    };
    
    // Test receiving an event
    console.log('\n==================================================');
    console.log('DEBUGGING PUSHER CONNECTION');
    console.log(`Connection state: ${pusher.connection.state}`);
    console.log(`Subscribed to channel: server.${SERVER_ID}`);
    console.log('Waiting for events...');
    console.log('==================================================\n');
    
    return res.json(pusherInfo);
});

// Toggle continuous mode endpoint
app.get('/toggle-continuous', (req, res) => {
    if (CONTINUOUS_MODE) {
        // If turning off continuous mode and it's the only active test
        if (activeTests.size === 1 && activeTests.has('continuous-mode')) {
            activeTests.delete('continuous-mode');
            stopMonitoring();
        } else {
            activeTests.delete('continuous-mode');
        }
        
        res.json({ 
            status: 'continuous_mode_disabled',
            message: 'Continuous mode disabled. Monitoring will stop when all tests complete.'
        });
    } else {
        activeTests.add('continuous-mode');
        startMonitoring();
        res.json({ 
            status: 'continuous_mode_enabled',
            message: 'Continuous mode enabled. Monitoring will run regardless of test events.'
        });
    }
});

// Manual control endpoints
app.get('/start', (req, res) => {
    startMonitoring();
    res.json({ status: 'monitoring_started' });
});

app.get('/stop', (req, res) => {
    stopMonitoring();
    res.json({ status: 'monitoring_stopped' });
});

// Force collect metrics endpoint
app.get('/collect', async (req, res) => {
    const metrics = await collectMetrics();
    res.json(metrics);
});

// Start Express server
app.listen(PORT, () => {
    console.log(`Server monitor running at http://localhost:${PORT}`);
    console.log(`Monitoring server ID: ${SERVER_ID}`);
    console.log(`API URL: ${API_URL}`);
    console.log(`Pusher channel: server.${SERVER_ID}`);
    
    if (CONTINUOUS_MODE) {
        console.log('\n==================================================');
        console.log('CONTINUOUS MONITORING MODE ENABLED');
        console.log('Pusher events will be ignored. Monitoring will run continuously.');
        console.log('==================================================\n');
        
        // Start monitoring immediately
        activeTests.add('continuous-mode');
        setTimeout(() => {
            startMonitoring();
        }, 1000); // Short delay to ensure everything is initialized
    } else {
        console.log('\nMonitoring will start when test events are received via Pusher');
        console.log(`To start monitoring manually, visit http://localhost:${PORT}/start`);
    }
});

// Handle exit gracefully
process.on('SIGINT', () => {
    console.log('Shutting down server monitor...');
    stopMonitoring();
    process.exit(0);
}); 