<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeatingArrangement extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id', 'room_id', 'name', 'layout', 'is_active',
    ];

    protected $casts = [
        'layout' => 'array',
        'is_active' => 'boolean',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }
}
