<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WhiteboardElement extends Model
{
    use HasFactory;

    protected $fillable = [
        'whiteboard_id', 'type', 'data', 'layer', 'created_by_type', 'created_by_id',
    ];

    protected $casts = [
        'data' => 'array',
        'layer' => 'integer',
    ];

    public function whiteboard(): BelongsTo
    {
        return $this->belongsTo(Whiteboard::class);
    }

    public function creator(): MorphTo
    {
        return $this->morphTo('created_by');
    }
}
