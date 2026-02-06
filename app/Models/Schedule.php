<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'subject_id', 
        'teacher_id', 
        'day', 
        'start_time', 
        'end_time', 
        'room',
        'room_id',
        'school_year',
    ];

    public function section() { return $this->belongsTo(Section::class); }
    public function subject() { return $this->belongsTo(Subject::class); }
    public function teacher() { return $this->belongsTo(Teacher::class); }
    public function roomModel() { return $this->belongsTo(Room::class, 'room_id'); }

    /**
     * Check if a teacher has a schedule conflict.
     */
    public static function hasTeacherConflict(int $teacherId, string $day, string $startTime, string $endTime, ?int $excludeId = null): bool
    {
        $query = self::where('teacher_id', $teacherId)
            ->where('day', $day)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Check if a room has a schedule conflict.
     */
    public static function hasRoomConflict(string $room, string $day, string $startTime, string $endTime, ?int $excludeId = null): bool
    {
        $query = self::where('room', $room)
            ->where('day', $day)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Check if a section has a schedule conflict.
     */
    public static function hasSectionConflict(int $sectionId, string $day, string $startTime, string $endTime, ?int $excludeId = null): bool
    {
        $query = self::where('section_id', $sectionId)
            ->where('day', $day)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}