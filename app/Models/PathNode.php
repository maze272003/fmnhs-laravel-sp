<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PathNode extends Model
{
    use HasFactory;

    protected $fillable = [
        'learning_path_id', 'title', 'content', 'type', 'difficulty',
        'order', 'prerequisites', 'estimated_minutes',
    ];

    protected $casts = [
        'order' => 'integer',
        'prerequisites' => 'array',
        'estimated_minutes' => 'integer',
    ];

    public function learningPath(): BelongsTo
    {
        return $this->belongsTo(LearningPath::class);
    }
}
