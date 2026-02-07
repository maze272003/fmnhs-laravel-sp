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
        'to_grade_level',   // This can now accept "Alumni" (String)
        'from_school_year',
        'to_school_year',
        'from_section_id',
        'to_section_id',
        'promoted_by',
    ];

    /**
     * Get the student associated with the promotion.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the section the student was promoted FROM.
     */
    public function fromSection()
    {
        return $this->belongsTo(Section::class, 'from_section_id');
    }

    /**
     * Get the section the student was promoted TO.
     */
    public function toSection()
    {
        return $this->belongsTo(Section::class, 'to_section_id');
    }
}