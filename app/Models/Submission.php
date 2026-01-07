<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    // Idagdag ang 'submitted_at' dito
    protected $fillable = [
        'assignment_id', 
        'student_id', 
        'file_path', 
        'remarks', 
        'submitted_at'
    ];

    /**
     * Cast 'submitted_at' as a datetime object para 
     * madali itong ma-format sa Blade templates.
     */
    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    // Relationships
    public function assignment() 
    { 
        return $this->belongsTo(Assignment::class); 
    }

    public function student() 
    { 
        return $this->belongsTo(Student::class); 
    }
}