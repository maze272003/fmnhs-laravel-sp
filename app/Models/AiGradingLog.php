<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiGradingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_id', 'ai_score', 'ai_feedback', 'human_score',
        'rubric_data', 'status',
    ];

    protected $casts = [
        'ai_score' => 'decimal:2',
        'human_score' => 'decimal:2',
        'rubric_data' => 'array',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }
}
