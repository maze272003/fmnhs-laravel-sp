<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Presentation extends Model
{
    use HasFactory;

    protected $fillable = [
        'conference_id', 'title', 'file_path', 'slide_count',
        'created_by_type', 'created_by_id',
    ];

    protected $casts = [
        'slide_count' => 'integer',
    ];

    public function conference(): BelongsTo
    {
        return $this->belongsTo(VideoConference::class, 'conference_id');
    }

    public function slides(): HasMany
    {
        return $this->hasMany(Slide::class);
    }

    public function creator(): MorphTo
    {
        return $this->morphTo('created_by');
    }
}
