<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Whiteboard extends Model
{
    use HasFactory;

    protected $fillable = [
        'conference_id', 'title', 'session_data', 'created_by_type', 'created_by_id',
    ];

    protected $casts = [
        'session_data' => 'array',
    ];

    public function conference(): BelongsTo
    {
        return $this->belongsTo(VideoConference::class, 'conference_id');
    }

    public function elements(): HasMany
    {
        return $this->hasMany(WhiteboardElement::class);
    }

    public function creator(): MorphTo
    {
        return $this->morphTo('created_by');
    }
}
