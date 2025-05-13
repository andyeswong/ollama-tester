<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SetupOllamaTester extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ollama:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up the Ollama Tester application (migrations, queue tables)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up Ollama Tester...');

        $this->info('Running migrations...');
        Artisan::call('migrate', ['--force' => true]);
        $this->info('Migrations completed.');

        $this->info('Creating queue tables...');
        Artisan::call('queue:table');
        Artisan::call('migrate', ['--force' => true]);
        $this->info('Queue tables created.');

        $this->info('Ollama Tester setup completed successfully!');
        $this->info('');
        $this->info('To start the application:');
        $this->info('1. Run "php artisan serve" to start the web server');
        $this->info('2. Run "php artisan queue:work --queue=ollama-tests" to process tests in the background');
        
        return Command::SUCCESS;
    }
}
