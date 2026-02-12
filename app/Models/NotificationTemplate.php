<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'subject', 'body', 'type', 'channels', 'is_active',
    ];

    protected $casts = [
        'channels' => 'array',
        'is_active' => 'boolean',
    ];
}
