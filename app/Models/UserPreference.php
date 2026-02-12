<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_type', 'user_id', 'theme', 'font_size', 'language',
        'notification_settings', 'preferences',
    ];

    protected $casts = [
        'notification_settings' => 'array',
        'preferences' => 'array',
    ];

    public function user(): MorphTo
    {
        return $this->morphTo();
    }
}
