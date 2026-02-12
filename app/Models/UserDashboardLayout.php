<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserDashboardLayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_type', 'user_id', 'widgets', 'layout',
    ];

    protected $casts = [
        'widgets' => 'array',
        'layout' => 'array',
    ];

    public function user(): MorphTo
    {
        return $this->morphTo();
    }
}
