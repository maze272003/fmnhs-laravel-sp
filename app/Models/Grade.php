<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'teacher_id',
        'subject_id',
        'quarter',
        'grade_value',      // Matches your database column
        'school_year',
        'school_year_id',   // <--- UPDATED: Changed from 'school_year' to 'school_year_id'
        'is_locked',
        'locked_at',
        'locked_by',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
    ];

    /**
     * Scope: locked grades.
     */
    public function scopeLocked($query)
    {
        return $query->where('is_locked', true);
    }

    /**
     * Scope: unlocked grades.
     */
    public function scopeUnlocked($query)
    {
        return $query->where('is_locked', false);
    }

    // =================RELATIONSHIPS=================

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * NEW: Relationship to School Year Config
     */
    public function schoolYearConfig()
    {
        return $this->belongsTo(SchoolYearConfig::class, 'school_year_id');
    }

    public function setSchoolYearAttribute(?string $schoolYear): void
    {
        if (!$schoolYear) {
            $this->attributes['school_year_id'] = null;
            return;
        }

        $config = SchoolYearConfig::firstOrCreate(
            ['school_year' => $schoolYear],
            [
                'start_date' => now()->startOfYear(),
                'end_date' => now()->endOfYear(),
                'status' => 'upcoming',
                'is_active' => false,
            ]
        );

        $this->attributes['school_year_id'] = $config->id;
    }

    public function getSchoolYearAttribute(): ?string
    {
        return $this->schoolYearConfig?->school_year;
    }
}
