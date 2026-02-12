<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Slide extends Model
{
    use HasFactory;

    protected $fillable = [
        'presentation_id', 'order', 'content', 'image_path', 'notes',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function presentation(): BelongsTo
    {
        return $this->belongsTo(Presentation::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(SlideView::class);
    }
}
