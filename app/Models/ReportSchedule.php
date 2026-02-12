<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id', 'frequency', 'next_run_at', 'settings', 'is_active',
    ];

    protected $casts = [
        'next_run_at' => 'datetime',
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
