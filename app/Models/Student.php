<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; 
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Student extends Authenticatable 
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'lrn',
        'first_name',
        'last_name',
        'email',
        'password',
        'section_id',
        'avatar',
        'enrollment_type',
        'enrollment_status',
        'is_alumni',
        'school_year',
    ];

    protected $casts = [
        'is_alumni' => 'boolean',
    ];

    protected $hidden = ['password', 'remember_token'];

    // Get the section (and grade level/advisor) for this student
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function grades() { return $this->hasMany(Grade::class); }

    public function promotionHistories()
    {
        return $this->hasMany(PromotionHistory::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    /**
     * Scope: only alumni students.
     */
    public function scopeAlumni($query)
    {
        return $query->where('is_alumni', true);
    }

    /**
     * Scope: only active (non-alumni) students.
     */
    public function scopeActive($query)
    {
        return $query->where('is_alumni', false);
    }

    /**
     * Check if this student is a new enrollee.
     * Grade 7 students are always new enrollees.
     * Transferees are tagged as "New Enrollee – Transferee".
     */
    public function isNewEnrollee(): bool
    {
        if (!$this->section) return false;
        return $this->section->grade_level === 7 || $this->enrollment_type === 'Transferee';
    }

    public function getEnrollmentBadgeAttribute(): ?string
    {
        if (!$this->section) return null;

        if ($this->enrollment_type === 'Transferee') {
            return 'New Enrollee – Transferee';
        }
        if ($this->section->grade_level === 7) {
            return 'Newly Enrolled';
        }
        return null;
    }

    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->avatar && $this->avatar !== 'default.png') {
                    return Storage::disk('s3')->url('avatars/' . $this->avatar);
                }
                $name = urlencode($this->first_name);
                return "https://ui-avatars.com/api/?name={$name}&background=2563eb&color=fff";
            }
        );
    }
}