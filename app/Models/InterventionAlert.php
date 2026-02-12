<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InterventionAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'alert_type', 'severity', 'description',
        'data', 'resolved_at', 'resolved_by_type', 'resolved_by_id',
    ];

    protected $casts = [
        'data' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function resolver(): MorphTo
    {
        return $this->morphTo('resolved_by');
    }
}
