<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningAnalytic extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'metric_type', 'metric_value', 'context', 'recorded_at',
    ];

    protected $casts = [
        'metric_value' => 'decimal:2',
        'context' => 'array',
        'recorded_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
