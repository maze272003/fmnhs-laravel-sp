<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StudyGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'subject_id', 'created_by_type',
        'created_by_id', 'max_members', 'is_active',
    ];

    protected $casts = [
        'max_members' => 'integer',
        'is_active' => 'boolean',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(StudyGroupMember::class);
    }

    public function creator(): MorphTo
    {
        return $this->morphTo('created_by');
    }
}
