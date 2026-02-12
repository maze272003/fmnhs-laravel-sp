<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudyGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'title', 'target_minutes', 'current_minutes',
        'period', 'is_completed', 'due_date',
    ];

    protected $casts = [
        'target_minutes' => 'integer',
        'current_minutes' => 'integer',
        'is_completed' => 'boolean',
        'due_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
