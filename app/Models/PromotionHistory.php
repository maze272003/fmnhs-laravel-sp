<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'from_grade_level',
        'to_grade_level',
        'from_school_year',
        'to_school_year',
        'from_section_id',
        'to_section_id',
        'promoted_by',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function fromSection()
    {
        return $this->belongsTo(Section::class, 'from_section_id');
    }

    public function toSection()
    {
        return $this->belongsTo(Section::class, 'to_section_id');
    }
}
