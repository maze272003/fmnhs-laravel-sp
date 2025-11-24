<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// 1. Palitan ang import na ito:
use Illuminate\Foundation\Auth\User as Authenticatable; 
use Illuminate\Notifications\Notifiable;

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
}