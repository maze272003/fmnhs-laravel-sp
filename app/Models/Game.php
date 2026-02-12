<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'conference_id', 'type', 'title', 'settings', 'status',
        'created_by_id', 'created_by_type', 'started_at', 'ended_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function conference(): BelongsTo
    {
        return $this->belongsTo(VideoConference::class, 'conference_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(GameSession::class);
    }

    public function creator(): MorphTo
    {
        return $this->morphTo('created_by');
    }
}
