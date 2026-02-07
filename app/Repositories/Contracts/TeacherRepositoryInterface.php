<?php

namespace App\Repositories\Contracts;

use App\Models\Teacher;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface TeacherRepositoryInterface
{
    public function paginateForAdmin(?string $search, bool $archived, int $perPage = 10): LengthAwarePaginator;

    public function getSectionsWithAdvisor(): Collection;

    public function getSubjects(): Collection;

    public function create(array $data): Teacher;

    public function update(Teacher $teacher, array $data): Teacher;

    public function clearAdvisoryByTeacherId(int $teacherId): void;

    public function assignAdvisorySection(int $teacherId, int $sectionId): void;

    public function archive(Teacher $teacher): void;

    public function restoreById(int $id): Teacher;
}
