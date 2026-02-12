<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecommendedContent extends Model
{
    use HasFactory;

    protected $table = 'recommended_content';

    protected $fillable = [
        'student_id', 'title', 'type', 'url', 'source',
        'relevance_score', 'is_viewed', 'feedback',
    ];

    protected $casts = [
        'relevance_score' => 'decimal:2',
        'is_viewed' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
