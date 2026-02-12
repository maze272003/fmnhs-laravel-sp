<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id', 'activity_type', 'description', 'duration_minutes',
        'metadata', 'performed_at',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'metadata' => 'array',
        'performed_at' => 'datetime',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
