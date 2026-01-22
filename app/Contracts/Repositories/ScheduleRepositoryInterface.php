<?php

namespace App\Contracts\Repositories;

use App\Models\Schedule;
use Illuminate\Database\Eloquent\Collection;

interface ScheduleRepositoryInterface extends BaseRepositoryInterface
{
    public function getBySection(int $sectionId): Collection;

    public function getByTeacher(int $teacherId): Collection;

    public function getByDay(string $day): Collection;

    public function getByTeacherAndDay(int $teacherId, string $day): Collection;

    public function getTeacherClasses(int $teacherId): Collection;

    public function getUniqueClasses(int $teacherId): Collection;
}
