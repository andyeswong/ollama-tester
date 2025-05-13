<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServerMetric extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'ollama_server_id',
        'active_tests',
        'collected_at',
        'cpu_usage',
        'cpu_cores',
        'memory_total',
        'memory_used',
        'memory_usage_percent',
        'cpu_temperature',
        'gpu_utilization',
        'gpu_memory_utilization',
        'gpu_memory_total',
        'gpu_memory_free',
        'gpu_memory_used',
        'gpu_temperature',
        'raw_data',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active_tests' => 'array',
        'cpu_cores' => 'array',
        'collected_at' => 'datetime',
        'raw_data' => 'array',
    ];
    
    /**
     * Get the server that the metric belongs to.
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(OllamaServer::class, 'ollama_server_id');
    }
    
    /**
     * Get the metrics for active tests.
     */
    public function scopeForTests($query, array $testIds)
    {
        return $query->whereJsonContains('active_tests', $testIds);
    }
    
    /**
     * Get the metrics for a specific server.
     */
    public function scopeForServer($query, $serverId)
    {
        return $query->where('ollama_server_id', $serverId);
    }
}
