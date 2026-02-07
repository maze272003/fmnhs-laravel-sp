<?php

namespace App\Repositories\Contracts;

use App\Models\Student;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface StudentRepositoryInterface
{
    public function getSectionsGroupedWithActiveCounts(): Collection;

    public function getAllSectionsOrdered(): Collection;

    public function getSchoolYearsDesc(): Collection;

    public function paginateActiveForAdmin(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findSection(?int $sectionId);

    public function findWithRecordOrFail(int $id): Student;

    public function create(array $data): Student;

    public function findOrFail(int $id): Student;

    public function update(Student $student, array $data): Student;

    public function findManyByIds(array $ids): Collection;

    public function archivedPaginated(int $perPage = 15): LengthAwarePaginator;

    public function findArchivedOrFail(int $id): Student;
}

