<?php

namespace App\Repositories\Eloquent;

use App\Models\SchoolYearConfig;
use App\Repositories\Contracts\SchoolYearRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SchoolYearRepository implements SchoolYearRepositoryInterface
{
    public function paginateDesc(int $perPage = 10): LengthAwarePaginator
    {
        return SchoolYearConfig::orderBy('school_year', 'desc')->paginate($perPage);
    }

    public function create(array $data): SchoolYearConfig
    {
        return SchoolYearConfig::create($data);
    }

    public function findOrFail(int $id): SchoolYearConfig
    {
        return SchoolYearConfig::findOrFail($id);
    }

    public function findBySchoolYear(string $schoolYear): ?SchoolYearConfig
    {
        return SchoolYearConfig::where('school_year', $schoolYear)->first();
    }

    public function getActive(): ?SchoolYearConfig
    {
        return SchoolYearConfig::where('is_active', true)->first();
    }

    public function deactivateAllActive(): void
    {
        SchoolYearConfig::where('is_active', true)->update(['is_active' => false]);
    }

    public function update(SchoolYearConfig $config, array $data): SchoolYearConfig
    {
        $config->update($data);
        return $config->fresh();
    }
}
