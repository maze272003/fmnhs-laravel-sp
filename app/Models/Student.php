<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// 1. Palitan ang import na ito:
use Illuminate\Foundation\Auth\User as Authenticatable; 
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

// 2. Palitan ang extends:
class Student extends Authenticatable 
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'lrn',
        'first_name',
        'last_name',
        'email',
        'password',
        'section_id', // Link to the new sections table
        'avatar'
    ];

    protected $hidden = ['password', 'remember_token'];

    // Get the section (and grade level/advisor) for this student
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function grades() { return $this->hasMany(Grade::class); }

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