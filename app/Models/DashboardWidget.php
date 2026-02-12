<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashboardWidget extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'type', 'component', 'default_settings', 'is_active', 'roles',
    ];

    protected $casts = [
        'default_settings' => 'array',
        'is_active' => 'boolean',
        'roles' => 'array',
    ];
}
