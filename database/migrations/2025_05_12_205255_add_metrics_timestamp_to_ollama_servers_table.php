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
        Schema::table('ollama_servers', function (Blueprint $table) {
            $table->timestamp('last_metrics_at')->nullable()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ollama_servers', function (Blueprint $table) {
            $table->dropColumn('last_metrics_at');
        });
    }
};
