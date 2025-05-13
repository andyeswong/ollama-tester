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
        Schema::create('server_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ollama_server_id')->constrained('ollama_servers')->onDelete('cascade');
            $table->json('active_tests')->nullable();
            $table->timestamp('collected_at');
            
            // CPU metrics
            $table->float('cpu_usage')->nullable();
            $table->json('cpu_cores')->nullable();
            
            // Memory metrics
            $table->bigInteger('memory_total')->nullable();
            $table->bigInteger('memory_used')->nullable();
            $table->float('memory_usage_percent')->nullable();
            
            // Temperature metrics
            $table->float('cpu_temperature')->nullable();
            
            // GPU metrics
            $table->float('gpu_utilization')->nullable();
            $table->float('gpu_memory_utilization')->nullable();
            $table->bigInteger('gpu_memory_total')->nullable();
            $table->bigInteger('gpu_memory_free')->nullable();
            $table->bigInteger('gpu_memory_used')->nullable();
            $table->float('gpu_temperature')->nullable();
            
            // Raw data for any additional metrics
            $table->json('raw_data')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('server_metrics');
    }
};
