<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TutoringSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'tutor_id', 'student_id', 'subject_id', 'scheduled_at',
        'completed_at', 'notes', 'rating', 'status',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'rating' => 'integer',
    ];

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(PeerTutor::class, 'tutor_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
