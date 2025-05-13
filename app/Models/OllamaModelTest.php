<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OllamaModelTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'ollama_server_id',
        'model_name',
        'prompt',
        'response',
        'response_time',
        'metadata',
        'status',
    ];

    protected $casts = [
        'metadata' => 'array',
        'response_time' => 'float',
    ];

    /**
     * Get the server that this test belongs to
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(OllamaServer::class, 'ollama_server_id');
    }
} 