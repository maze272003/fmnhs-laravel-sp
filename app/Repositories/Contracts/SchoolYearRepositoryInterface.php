<?php

namespace App\Repositories\Contracts;

use App\Models\SchoolYearConfig;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SchoolYearRepositoryInterface
{
    public function paginateDesc(int $perPage = 10): LengthAwarePaginator;

    public function create(array $data): SchoolYearConfig;

    public function findOrFail(int $id): SchoolYearConfig;

    public function findBySchoolYear(string $schoolYear): ?SchoolYearConfig;

    public function getActive(): ?SchoolYearConfig;

    public function deactivateAllActive(): void;

    public function update(SchoolYearConfig $config, array $data): SchoolYearConfig;
}
