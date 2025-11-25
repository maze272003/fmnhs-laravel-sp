<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    // DAPAT KASAMA ANG 'image' DITO
    protected $fillable = [
        'title', 
        'content', 
        'author_name', 
        'role', 
        'image' // <--- IDAGDAG MO ITO
    ];
}