<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $guard = 'student';

    protected $fillable = [
        'first_name',
        'last_name',
        'lrn',
        'email',
        'password',
        'section_id',
        'school_year',
        'school_year_id',
        'enrollment_type',
        'enrollment_status',
        'is_alumni',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_alumni' => 'boolean',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function scopeAlumni($query)
    {
        return $query->where('is_alumni', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_alumni', false)->whereNull('deleted_at');
    }

    public function schoolYearConfig()
    {
        return $this->belongsTo(SchoolYearConfig::class, 'school_year_id');
    }

    // THIS IS THE MISSING RELATIONSHIP causing your 500 Error
    public function promotionHistory()
    {
        return $this->hasMany(PromotionHistory::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
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

    public function getEnrollmentBadgeAttribute(): ?string
    {
        if ($this->is_alumni) {
            return 'Alumni';
        }

        if ($this->enrollment_type === 'Transferee') {
            return 'New Enrollee – Transferee';
        }

        if ((int) ($this->section?->grade_level ?? 0) === 7) {
            return 'Newly Enrolled';
        }

        if ($this->enrollment_status === 'Promoted') {
            return 'Promoted';
        }

        return null;
    }
}
