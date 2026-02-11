<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'type', 'requirements',
        'points_reward', 'badge_id', 'is_repeatable', 'is_active',
    ];

    protected $casts = [
        'requirements' => 'array',
        'points_reward' => 'integer',
        'is_repeatable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_achievements')
            ->withPivot('completed_at', 'completion_count', 'completion_data')
            ->withTimestamps();
    }
}
