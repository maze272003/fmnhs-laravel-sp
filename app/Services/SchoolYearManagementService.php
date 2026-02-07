<?php

namespace App\Services;

use App\Models\AuditTrail;
use App\Models\SchoolYearConfig;
use App\Repositories\Contracts\SchoolYearRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SchoolYearManagementService
{
    public function __construct(private readonly SchoolYearRepositoryInterface $schoolYears)
    {
    }

    public function create(array $validated, ?object $adminUser = null): SchoolYearConfig
    {
        $validated['status'] = 'upcoming';
        $validated['is_active'] = false;

        $config = $this->schoolYears->create($validated);

        AuditTrail::log(
            'SchoolYearConfig',
            $config->id,
            'created',
            null,
            null,
            $config->toArray(),
            'admin',
            $adminUser?->id,
            $adminUser?->name ?? 'Admin'
        );

        return $config;
    }

    public function activate(int $id, ?object $adminUser = null): SchoolYearConfig
    {
        $config = $this->schoolYears->findOrFail($id);
        $oldStatus = $config->status;

        $updated = DB::transaction(function () use ($config) {
            $this->schoolYears->deactivateAllActive();
            return $this->schoolYears->update($config, [
                'is_active' => true,
                'status' => 'active',
            ]);
        });

        AuditTrail::log(
            'SchoolYearConfig',
            $updated->id,
            'updated',
            'status',
            $oldStatus,
            'active',
            'admin',
            $adminUser?->id,
            $adminUser?->name ?? 'Admin'
        );

        return $updated;
    }

    public function close(int $id, ?object $adminUser = null): SchoolYearConfig
    {
        $config = $this->schoolYears->findOrFail($id);
        if ($config->status === 'closed') {
            throw ValidationException::withMessages([
                'school_year' => 'School year is already closed.',
            ]);
        }

        $oldStatus = $config->status;
        $updated = $this->schoolYears->update($config, [
            'is_active' => false,
            'status' => 'closed',
        ]);

        AuditTrail::log(
            'SchoolYearConfig',
            $updated->id,
            'updated',
            'status',
            $oldStatus,
            'closed',
            'admin',
            $adminUser?->id,
            $adminUser?->name ?? 'Admin'
        );

        return $updated;
    }

    public function resolveSchoolYearId(?string $schoolYear, ?int $fallbackId = null): ?int
    {
        if ($fallbackId) {
            return $fallbackId;
        }

        if ($schoolYear) {
            return $this->schoolYears->findBySchoolYear($schoolYear)?->id;
        }

        return $this->schoolYears->getActive()?->id;
    }
}
