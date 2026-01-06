<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
   protected $fillable = [
       'teacher_id', 
       'subject_id', 
       'section_id', // Changed from 'section'
       'title', 
       'description', 
       'file_path', 
       'deadline'
   ];

    public function section() { return $this->belongsTo(Section::class); }
    public function submissions() { return $this->hasMany(Submission::class); }
    public function subject() { return $this->belongsTo(Subject::class); }
    public function teacher() { return $this->belongsTo(Teacher::class); }
}