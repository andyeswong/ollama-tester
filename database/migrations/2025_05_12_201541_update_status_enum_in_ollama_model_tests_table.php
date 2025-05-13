<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For SQLite we need to recreate the table
        if (DB::connection()->getDriverName() === 'sqlite') {
            // First create a temporary table
            Schema::create('ollama_model_tests_temp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ollama_server_id');
                $table->string('model_name');
                $table->text('prompt');
                $table->text('response')->nullable();
                $table->float('response_time')->nullable();
                $table->json('metadata')->nullable();
                $table->enum('status', ['pending', 'in_progress', 'completed', 'failed'])->default('pending');
                $table->timestamps();
            });
            
            // Copy data from old table to new table
            DB::statement('INSERT INTO ollama_model_tests_temp SELECT * FROM ollama_model_tests');
            
            // Drop old table
            Schema::drop('ollama_model_tests');
            
            // Rename temp table to the original name
            Schema::rename('ollama_model_tests_temp', 'ollama_model_tests');
            
            // Add back foreign key constraints
            Schema::table('ollama_model_tests', function (Blueprint $table) {
                $table->foreign('ollama_server_id')->references('id')->on('ollama_servers')->onDelete('cascade');
            });
        } else {
            // For other databases, we can use the ALTER TABLE statement
            DB::statement("ALTER TABLE ollama_model_tests MODIFY COLUMN status ENUM('pending', 'in_progress', 'completed', 'failed') DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // For SQLite we need to recreate the table
        if (DB::connection()->getDriverName() === 'sqlite') {
            // First create a temporary table
            Schema::create('ollama_model_tests_temp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ollama_server_id');
                $table->string('model_name');
                $table->text('prompt');
                $table->text('response')->nullable();
                $table->float('response_time')->nullable();
                $table->json('metadata')->nullable();
                $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
                $table->timestamps();
            });
            
            // Copy data that matches the old schema
            DB::statement("INSERT INTO ollama_model_tests_temp SELECT * FROM ollama_model_tests WHERE status != 'in_progress'");
            
            // For in_progress records, set them to pending
            DB::statement("INSERT INTO ollama_model_tests_temp SELECT id, ollama_server_id, model_name, prompt, response, response_time, metadata, 'pending', created_at, updated_at FROM ollama_model_tests WHERE status = 'in_progress'");
            
            // Drop old table
            Schema::drop('ollama_model_tests');
            
            // Rename temp table to the original name
            Schema::rename('ollama_model_tests_temp', 'ollama_model_tests');
            
            // Add back foreign key constraints
            Schema::table('ollama_model_tests', function (Blueprint $table) {
                $table->foreign('ollama_server_id')->references('id')->on('ollama_servers')->onDelete('cascade');
            });
        } else {
            // For other databases, we can use the ALTER TABLE statement
            DB::statement("ALTER TABLE ollama_model_tests MODIFY COLUMN status ENUM('pending', 'completed', 'failed') DEFAULT 'pending'");
        }
    }
};
