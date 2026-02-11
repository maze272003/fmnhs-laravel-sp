<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id', 'question', 'type', 'options', 'correct_answers',
        'points', 'time_limit', 'order', 'image_path', 'explanation',
    ];

    protected $casts = [
        'options' => 'array',
        'correct_answers' => 'array',
        'points' => 'integer',
        'time_limit' => 'integer',
        'order' => 'integer',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(QuizResponse::class, 'question_id');
    }

    public function isMultipleChoice(): bool
    {
        return $this->type === 'multiple_choice';
    }

    public function isTrueFalse(): bool
    {
        return $this->type === 'true_false';
    }

    public function isPoll(): bool
    {
        return $this->type === 'poll';
    }
}
