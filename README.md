# Ollama Tester

A comprehensive tool for testing and benchmarking Ollama models. This application helps you evaluate model performance, manage multiple Ollama servers, and collect detailed metrics during testing.

## 🌟 Features

### Server Management
- Connect and manage multiple Ollama servers
- Monitor server health and status
- Track server resource usage

### Comprehensive Testing
- Test individual models with customizable prompts
- Compare performance between different models
- Evaluate model responses for accuracy and quality
- Collect detailed performance metrics

### Stress Testing
- Run parallel tests to evaluate server capacity
- Determine optimal model configurations
- Identify performance bottlenecks

### Real-time Monitoring
- Watch test results update in real-time
- Track token generation speeds
- Monitor system resource utilization during tests

### Performance Metrics
- Tokens per second (TPS) calculation
- Response generation time
- Token count analysis
- CPU, Memory, and GPU utilization metrics

## 🚀 Getting Started

### Prerequisites
- PHP 8.1 or higher
- Composer
- Node.js and NPM
- Ollama running on at least one server

### Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/ollama-tester.git
cd ollama-tester
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install JavaScript dependencies:
```bash
npm install
```

4. Copy the environment file:
```bash
cp .env.example .env
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Configure your database in the `.env` file:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ollama_tester
DB_USERNAME=root
DB_PASSWORD=
```

7. Run migrations:
```bash
php artisan migrate
```

8. Build assets:
```bash
npm run build
```

9. Start the development server:
```bash
php artisan serve
```

10. Start the queue worker with extended timeout:
```bash
./start-queue-worker.sh
```

## ⚙️ Configuration Options

### Environment Variables
The application can be configured using the following environment variables in your `.env` file:

- `APP_ENV`: Set to `production` to enforce HTTPS
- `OLLAMA_REQUEST_TIMEOUT`: Maximum time (in seconds) for Ollama API requests before timeout (default: 300 seconds)
- `DB_CONNECTION`, `DB_HOST`, etc.: Database configuration options
- `QUEUE_CONNECTION`: Queue driver for background jobs

### Request Timeouts
By default, requests to Ollama servers will timeout after 300 seconds (5 minutes). For models that require longer processing times:

1. Set `OLLAMA_REQUEST_TIMEOUT` in your `.env` file to a higher value (in seconds)
2. For very large models or complex prompts, consider increasing this to 600 or more

### Queue Worker Timeouts
The application uses Laravel's queue system to process Ollama requests in the background. For long-running tests:

1. Use the provided script to start the queue worker with an extended timeout:
   ```bash
   ./start-queue-worker.sh
   ```

2. The default timeout in the script is set to 1 hour (3600 seconds). Modify the `TIMEOUT` variable in the script if needed.

3. If you encounter `ProcessTimedOutException` errors, this indicates the queue worker is timing out and you should use this script.

## 📖 Usage

### Adding an Ollama Server
1. Register or login to your account
2. Navigate to "Servers" in the dashboard
3. Click "Add Server"
4. Enter your Ollama server URL (e.g., http://localhost:11434)
5. Save the server configuration

### Running Tests
1. Select a server from your dashboard
2. Choose a model to test
3. Configure test parameters (prompts, temperature, etc.)
4. Start the test
5. View real-time results as they are generated

### Comparing Test Results
1. Navigate to the "Tests" section
2. Select multiple test runs to compare
3. Review side-by-side comparisons of:
   - Response quality
   - Generation speed
   - Token efficiency
   - Server resource utilization

## 🔒 Security

### HTTPS Enforcement
- The application automatically forces all URLs to use HTTPS when running in production mode
- This behavior is controlled by the `APP_ENV` setting in your `.env` file
- Set `APP_ENV=production` to enable HTTPS enforcement
- No additional configuration is required for this security feature

## 🧰 Technology Stack

- **Backend**: Laravel PHP Framework
- **Frontend**: Vue.js with Tailwind CSS
- **Database**: MySQL/SQLite
- **Styling**: Glassmorphic design with custom utility classes

## 👥 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🙏 Acknowledgements

- [Ollama](https://github.com/ollama/ollama) for the amazing local LLM server
- [Laravel](https://laravel.com/) for the robust PHP framework
- [Tailwind CSS](https://tailwindcss.com/) for the utility-first CSS framework
- [Vue.js](https://vuejs.org/) for the progressive JavaScript framework 