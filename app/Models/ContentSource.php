<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'type', 'base_url', 'api_key', 'settings', 'is_active',
    ];

    protected $hidden = [
        'api_key',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];
}
