<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlideView extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'slide_id', 'student_id', 'viewed_at', 'duration_seconds',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
        'duration_seconds' => 'integer',
    ];

    public function slide(): BelongsTo
    {
        return $this->belongsTo(Slide::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
