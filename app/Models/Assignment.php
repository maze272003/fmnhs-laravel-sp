<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = [
        'teacher_id',
        'subject_id',
        'section_id',
        'title',
        'description',
        'file_path',
        'deadline',
    ];

    protected $casts = [
        'deadline' => 'datetime',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}