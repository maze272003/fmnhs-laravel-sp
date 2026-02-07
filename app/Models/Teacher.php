<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // <--- IMPORANTE
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes; //
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Authenticatable
{
    use SoftDeletes;
    use HasFactory, Notifiable;

    protected $fillable = ['employee_id', 'first_name', 'last_name', 'email', 'password', 'department'];
    protected $hidden = ['password', 'remember_token'];

    // If the teacher is an advisor, this returns their section
    public function advisorySection()
    {
        return $this->hasOne(Section::class, 'teacher_id');
    }

    public function videoConferences(): HasMany
    {
        return $this->hasMany(VideoConference::class);
    }

    public function grades() { return $this->hasMany(Grade::class); }
}
