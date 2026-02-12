<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationChannel extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_type', 'user_id', 'channel', 'settings', 'is_enabled',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_enabled' => 'boolean',
    ];

    public function user(): MorphTo
    {
        return $this->morphTo();
    }
}
