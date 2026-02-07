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
}