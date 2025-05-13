# Ollama Tester

This is a Laravel 12 application for testing [Ollama](https://ollama.ai/) servers. It allows you to:

1. Connect to multiple Ollama servers
2. List available models on each server
3. Test single models with various prompts
4. Test multiple models with the same prompt simultaneously
5. Run performance tests with multiple iterations
6. Compare responses and performance between different models

## Requirements

- PHP 8.2+
- Composer
- Node.js & NPM
- SQLite or another database system (MySQL, PostgreSQL)

## Installation

1. Clone the repository
2. Install PHP dependencies: `composer install`
3. Install frontend dependencies: `npm install`
4. Build assets: `npm run build`
5. Configure your database in `.env`
6. Run migrations: `php artisan migrate`
7. Start the server: `php artisan serve`
8. Start the queue worker: `php artisan queue:work --queue=ollama-tests`

## Usage

### Adding an Ollama Server

1. Navigate to the home page
2. Click "Add Server"
3. Enter the server name and URL (e.g., `http://localhost:11434`)
4. Save the server

### Testing Models

1. From the servers list, click "View" on a server
2. You'll see a list of available models on that server
3. Click "Run Single Test" to test a specific model
4. Select the model, enter a prompt, and set the number of iterations
5. Click "Run Test" to execute

### Testing Multiple Models

1. From the server view page, click "Test Multiple Models"
2. Select the models you want to test
3. Enter a prompt that will be sent to all selected models
4. Set the number of iterations per model
5. Click "Run Tests" to execute

### Viewing and Comparing Results

1. Navigate to the "Tests" tab for a server
2. View individual test results by clicking "View"
3. To compare multiple tests, select them using the checkboxes
4. Click "Compare Selected Tests" to see a side-by-side comparison

## Queue Processing

Tests are processed in the background for better performance. Make sure to run the queue worker:

```
php artisan queue:work --queue=ollama-tests
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT). 