<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// Eto ang kailangang-kailangan para gumana ang return type hint:
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
        'school_year',
    ];

    /**
     * Ang Teacher na nagsisilbing Advisor ng section na ito.
     * Gagamitin natin ang pangalang 'teacher' para mag-match sa Controller logic.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    /**
     * Kung gusto mong 'advisor' ang itawag, pwede mong i-alias ito.
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
}