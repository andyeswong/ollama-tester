#!/bin/bash

# Script to start Laravel queue worker with extended timeout
# This prevents "ProcessTimedOutException" errors for long-running Ollama tests

# Set timeout to 1 hour (3600 seconds) - adjust as needed
TIMEOUT=3600

# Start the queue worker with extended timeout
php artisan queue:work --queue=ollama-tests --timeout=$TIMEOUT

# If you want to run in daemon mode, uncomment the next line instead
# php artisan queue:work --queue=ollama-tests --timeout=$TIMEOUT --daemon 