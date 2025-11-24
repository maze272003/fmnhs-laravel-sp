<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // <--- IMPORANTE
use Illuminate\Notifications\Notifiable;

class Teacher extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'employee_id', 'first_name', 'last_name', 
        'email', 'password', 'department'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];
}