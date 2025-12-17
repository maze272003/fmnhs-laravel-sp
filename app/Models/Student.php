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
        'grade_level',
        'strand',
        'section',
    ];

    // 3. Siguraduhing naka-hide ang password sa arrays
    protected $hidden = [
        'password',
        'remember_token',
    ];
    public function grades() { return $this->hasMany(Grade::class); }

    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Check if avatar exists and isn't the legacy default
                if ($this->avatar && $this->avatar !== 'default.png') {
                    return Storage::disk('s3')->url('avatars/' . $this->avatar);
                }

                // Fallback to UI Avatars
                $name = urlencode($this->first_name);
                return "https://ui-avatars.com/api/?name={$name}&background=2563eb&color=fff";
            }
        );
    }
}