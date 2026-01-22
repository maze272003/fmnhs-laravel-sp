<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ScheduleRepositoryInterface;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Collection;

class ScheduleRepository extends BaseRepository implements ScheduleRepositoryInterface
{
    public function __construct(Schedule $model)
    {
        parent::__construct($model);
    }

    public function getBySection(int $sectionId): Collection
    {
        return $this->model->where('section_id', $sectionId)
            ->with(['subject', 'teacher'])
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();
    }

    public function getByTeacher(int $teacherId): Collection
    {
        return $this->model->where('teacher_id', $teacherId)
            ->with(['subject', 'section'])
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();
    }

    public function getByDay(string $day): Collection
    {
        return $this->model->where('day', $day)
            ->with(['subject', 'teacher', 'section'])
            ->orderBy('start_time')
            ->get();
    }

    public function getByTeacherAndDay(int $teacherId, string $day): Collection
    {
        return $this->model->where('teacher_id', $teacherId)
            ->where('day', $day)
            ->with(['subject', 'section'])
            ->orderBy('start_time')
            ->get();
    }

    public function getTeacherClasses(int $teacherId): Collection
    {
        return $this->model->where('teacher_id', $teacherId)
            ->with(['subject', 'section'])
            ->get();
    }

    public function getUniqueClasses(int $teacherId): Collection
    {
        $schedules = $this->model->where('teacher_id', $teacherId)
            ->with(['subject', 'section'])
            ->get();

        return $schedules->unique(function($item) {
            return $item->subject_id . '-' . $item->section_id;
        });
    }
}
