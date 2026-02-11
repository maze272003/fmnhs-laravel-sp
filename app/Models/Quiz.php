<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'conference_id', 'teacher_id', 'title', 'description', 'type', 'status',
        'time_limit', 'show_correct_answers', 'show_leaderboard',
        'randomize_questions', 'randomize_options', 'passing_score',
        'started_at', 'ended_at', 'settings',
    ];

    protected $casts = [
        'time_limit' => 'integer',
        'show_correct_answers' => 'boolean',
        'show_leaderboard' => 'boolean',
        'randomize_questions' => 'boolean',
        'randomize_options' => 'boolean',
        'passing_score' => 'integer',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'settings' => 'array',
    ];

    public function conference(): BelongsTo
    {
        return $this->belongsTo(VideoConference::class, 'conference_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class, 'quiz_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(QuizResponse::class, 'quiz_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }
}
