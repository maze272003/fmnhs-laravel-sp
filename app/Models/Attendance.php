<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 
        'subject_id', 
        'teacher_id', 
        'section', 
        'date', 
        'status'
    ];

    // Existing relationships
    public function student() 
    { 
        return $this->belongsTo(Student::class); 
    }
    
    public function subject() 
    { 
        return $this->belongsTo(Subject::class); 
    }

    // --- IDAGDAG MO ITO (Ang kulang) ---
    public function teacher() 
    { 
        return $this->belongsTo(Teacher::class); 
    }
}