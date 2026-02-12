<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LessonPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id', 'subject_id', 'title', 'description', 'objectives',
        'activities', 'resources', 'duration_minutes', 'grade_level',
        'status', 'scheduled_date',
    ];

    protected $casts = [
        'objectives' => 'array',
        'activities' => 'array',
        'resources' => 'array',
        'duration_minutes' => 'integer',
        'scheduled_date' => 'date',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function lessonResources(): HasMany
    {
        return $this->hasMany(LessonResource::class);
    }
}
