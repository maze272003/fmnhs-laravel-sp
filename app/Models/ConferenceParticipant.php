<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConferenceParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'conference_id', 'actor_id', 'actor_type', 'display_name', 'role',
        'joined_at', 'left_at', 'duration_seconds', 'is_guest', 'device_info',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
        'is_guest' => 'boolean',
        'device_info' => 'array',
    ];

    public function conference(): BelongsTo
    {
        return $this->belongsTo(VideoConference::class, 'conference_id');
    }

    public function isOnline(): bool
    {
        return $this->joined_at !== null && $this->left_at === null;
    }
}
