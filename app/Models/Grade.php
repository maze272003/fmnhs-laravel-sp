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
        'grade_value',
        'school_year',
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

    /**
     * Get the student that owns the grade.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the teacher who gave the grade.
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the subject associated with the grade.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}