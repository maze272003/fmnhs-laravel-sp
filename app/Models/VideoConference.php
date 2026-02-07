<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoConference extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'section_id',
        'title',
        'slug',
        'is_active',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function canStudentJoin(Student $student): bool
    {
        if (! $this->is_active || $this->ended_at !== null) {
            return false;
        }

        if (! $student->section_id) {
            return false;
        }

        if ($this->section_id !== null) {
            return (int) $student->section_id === (int) $this->section_id;
        }

        return Schedule::where('teacher_id', $this->teacher_id)
            ->where('section_id', $student->section_id)
            ->exists()
            || Section::where('teacher_id', $this->teacher_id)
                ->whereKey($student->section_id)
                ->exists();
    }
}
