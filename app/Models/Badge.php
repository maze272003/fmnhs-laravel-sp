<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'icon', 'color', 'category',
        'unlock_criteria', 'points_value', 'is_active',
    ];

    protected $casts = [
        'unlock_criteria' => 'array',
        'points_value' => 'integer',
        'is_active' => 'boolean',
    ];

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_badges')
            ->withPivot('earned_at', 'note')
            ->withTimestamps();
    }
}
