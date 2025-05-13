<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ollama_model_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ollama_server_id')->constrained('ollama_servers')->onDelete('cascade');
            $table->string('model_name');
            $table->text('prompt');
            $table->text('response')->nullable();
            $table->float('response_time')->nullable()->comment('Time in seconds');
            $table->json('metadata')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ollama_model_tests');
    }
}; 