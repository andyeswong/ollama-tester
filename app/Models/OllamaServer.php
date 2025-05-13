<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OllamaServer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the tests for this server
     */
    public function tests(): HasMany
    {
        return $this->hasMany(OllamaModelTest::class);
    }
} 