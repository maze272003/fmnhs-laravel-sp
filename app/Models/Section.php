<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'grade_level',
        'strand',
        'teacher_id', // This is the Advisor ID
    ];

    // The Teacher who is the Advisor of this section
    public function advisor()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    // Students belonging to this section
    public function students()
    {
        return $this->hasMany(Student::class);
    }

    // Schedules for this section
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}