<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LearningPath extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'subject_id', 'difficulty_level',
        'created_by_type', 'created_by_id', 'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function nodes(): HasMany
    {
        return $this->hasMany(PathNode::class);
    }

    public function studentProgress(): HasMany
    {
        return $this->hasMany(StudentPathProgress::class);
    }

    public function creator(): MorphTo
    {
        return $this->morphTo('created_by');
    }
}
