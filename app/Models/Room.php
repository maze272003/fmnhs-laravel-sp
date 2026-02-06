<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'building',
        'capacity',
        'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean',
    ];

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Check if the room is available at a given day and time range.
     */
    public function isAvailableAt(string $day, string $startTime, string $endTime, ?int $excludeScheduleId = null): bool
    {
        $query = $this->schedules()
            ->where('day', $day)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where(function ($inner) use ($startTime, $endTime) {
                    $inner->where('start_time', '<', $endTime)
                          ->where('end_time', '>', $startTime);
                });
            });

        if ($excludeScheduleId) {
            $query->where('id', '!=', $excludeScheduleId);
        }

        return $query->count() === 0;
    }
}
