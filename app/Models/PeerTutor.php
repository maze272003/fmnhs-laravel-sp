<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeerTutor extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'subject_id', 'rating', 'is_available',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'is_available' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function tutoringSessions(): HasMany
    {
        return $this->hasMany(TutoringSession::class, 'tutor_id');
    }
}
