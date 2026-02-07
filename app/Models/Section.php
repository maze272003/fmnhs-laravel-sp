<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'grade_level',
        'strand',
        'teacher_id',
        'school_year_id', // FIXED: Changed from 'school_year' to match DB column
    ];

    /**
     * Relationship to the School Year Configuration.
     * This fixes the "Call to undefined relationship" error.
     */
    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYearConfig::class, 'school_year_id');
    }

    /**
     * The Teacher who is the Advisor of this section.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    /**
     * Alias for teacher() if you prefer calling it advisor.
     */
    public function advisor(): BelongsTo
    {
        return $this->teacher();
    }

    // Students belonging to this section
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    // Schedules for this section
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function videoConferences(): HasMany
    {
        return $this->hasMany(VideoConference::class);
    }
}
