<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BreakoutRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'conference_id', 'name', 'settings', 'duration_minutes', 'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'duration_minutes' => 'integer',
        'is_active' => 'boolean',
    ];

    public function conference(): BelongsTo
    {
        return $this->belongsTo(VideoConference::class, 'conference_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(BreakoutRoomParticipant::class);
    }
}
