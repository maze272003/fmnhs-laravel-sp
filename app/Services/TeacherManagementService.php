<?php

namespace App\Services;

use App\Models\Teacher;
use App\Repositories\Contracts\TeacherRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeacherManagementService
{
    public function __construct(private readonly TeacherRepositoryInterface $teachers)
    {
    }

    public function getAdminTeacherData(?string $search, bool $archived): array
    {
        return [
            'teachers' => $this->teachers->paginateForAdmin($search, $archived, 10),
            'sections' => $this->teachers->getSectionsWithAdvisor(),
            'subjects' => $this->teachers->getSubjects(),
            'viewArchived' => $archived,
        ];
    }

    public function create(array $validated): Teacher
    {
        $validated['password'] = Hash::make(Str::random(12));
        return $this->teachers->create($validated);
    }

    public function update(Teacher $teacher, array $validated, ?int $advisorySectionId): Teacher
    {
        return DB::transaction(function () use ($teacher, $validated, $advisorySectionId) {
            $updated = $this->teachers->update($teacher, [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'department' => $validated['department'],
            ]);

            $this->teachers->clearAdvisoryByTeacherId($teacher->id);

            if ($advisorySectionId) {
                $this->teachers->assignAdvisorySection($teacher->id, $advisorySectionId);
            }

            return $updated;
        });
    }

    public function archive(Teacher $teacher): void
    {
        $this->teachers->clearAdvisoryByTeacherId($teacher->id);
        $this->teachers->archive($teacher);
    }

    public function restore(int $id): Teacher
    {
        return $this->teachers->restoreById($id);
    }
}
