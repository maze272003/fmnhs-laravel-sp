<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'source_type', 'source_id', 'points', 'reason', 'details',
    ];

    protected $casts = [
        'points' => 'integer',
        'source_id' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
