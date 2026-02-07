<?php

namespace App\Services;

use App\Mail\StudentAccountCreated;
use App\Models\AuditTrail;
use App\Models\PromotionHistory;
use App\Models\SchoolYearConfig;
use App\Models\Student;
use App\Repositories\Contracts\StudentRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class StudentLifecycleService
{
    public function __construct(private readonly StudentRepositoryInterface $students)
    {
    }

    public function create(array $validated, ?object $adminUser = null): Student
    {
        $rawPassword = $validated['lrn'];
        $validated['password'] = Hash::make($rawPassword);
        $validated['enrollment_status'] = 'Enrolled';
        $validated['is_alumni'] = false;

        $student = $this->students->create($validated);

        AuditTrail::log('Student', $student->id, 'created', null, null, $student->toArray(), 'admin', $adminUser?->id);

        try {
            Mail::to($student->email)->send(new StudentAccountCreated($student, $rawPassword));
        } catch (\Throwable $exception) {
            logger()->error('Student account mail send failed', ['error' => $exception->getMessage()]);
        }

        return $student;
    }

    public function update(Student $student, array $validated, ?object $adminUser = null): Student
    {
        if ($student->is_alumni) {
            throw ValidationException::withMessages(['error' => 'Alumni records are read-only.']);
        }

        $oldData = $student->toArray();

        if (!empty($validated['new_password'])) {
            $validated['password'] = Hash::make($validated['new_password']);
        }

        unset($validated['new_password']);

        $updated = $this->students->update($student, $validated);

        AuditTrail::log('Student', $updated->id, 'updated', null, $oldData, $updated->toArray(), 'admin', $adminUser?->id);

        return $updated;
    }

    public function promote(array $studentIds, int $toSchoolYearId, ?int $toSectionId, ?object $adminUser = null): array
    {
        $targetSchoolYear = SchoolYearConfig::findOrFail($toSchoolYearId);
        $students = $this->students->findManyByIds($studentIds);

        $promotedCount = 0;
        $graduatedCount = 0;

        DB::transaction(function () use (
            $studentIds,
            $students,
            $toSectionId,
            $targetSchoolYear,
            $adminUser,
            &$promotedCount,
            &$graduatedCount
        ) {
            foreach ($studentIds as $studentId) {
                /** @var Student|null $student */
                $student = $students->get($studentId);
                if (!$student || $student->is_alumni) {
                    continue;
                }

                $fromSection = $student->section;
                if (!$fromSection) {
                    throw ValidationException::withMessages([
                        'error' => "Student {$student->id} has no section assigned and cannot be promoted.",
                    ]);
                }

                $isGraduating = (int) $fromSection->grade_level === 12;

                if (!$isGraduating && !$toSectionId) {
                    throw ValidationException::withMessages([
                        'error' => 'Regular promotion requires a destination section.',
                    ]);
                }

                PromotionHistory::create([
                    'student_id' => $student->id,
                    'from_grade_level' => (string) $fromSection->grade_level,
                    'to_grade_level' => $isGraduating ? 'Alumni' : (string) ((int) $fromSection->grade_level + 1),
                    'from_school_year' => $student->schoolYearConfig?->school_year ?? 'N/A',
                    'to_school_year' => $targetSchoolYear->school_year,
                    'from_section_id' => $fromSection->id,
                    'to_section_id' => $isGraduating ? null : $toSectionId,
                    'promoted_by' => $adminUser?->name ?? 'Admin',
                ]);

                $student->update([
                    'school_year_id' => $targetSchoolYear->id,
                    'enrollment_type' => 'Regular',
                    'enrollment_status' => $isGraduating ? 'Alumni' : 'Promoted',
                    'is_alumni' => $isGraduating,
                    'section_id' => $isGraduating ? null : $toSectionId,
                ]);

                AuditTrail::log(
                    'Student',
                    $student->id,
                    $isGraduating ? 'graduated' : 'promoted',
                    'grade_level',
                    $fromSection->grade_level,
                    $isGraduating ? 'Alumni' : ((int) $fromSection->grade_level + 1),
                    'admin',
                    $adminUser?->id
                );

                if ($isGraduating) {
                    $graduatedCount++;
                } else {
                    $promotedCount++;
                }
            }
        });

        return [
            'promoted_count' => $promotedCount,
            'graduated_count' => $graduatedCount,
        ];
    }

    public function archive(Student $student, ?object $adminUser = null): void
    {
        if ($student->is_alumni) {
            throw ValidationException::withMessages([
                'error' => 'Alumni records are permanent and cannot be archived manually.',
            ]);
        }

        $student->update(['enrollment_status' => 'Archived']);
        $student->delete();

        AuditTrail::log('Student', $student->id, 'archived', 'status', 'Enrolled', 'Archived', 'admin', $adminUser?->id);
    }

    public function restore(Student $student, ?object $adminUser = null): Student
    {
        $student->restore();
        $student->update(['enrollment_status' => 'Enrolled', 'is_alumni' => false]);

        AuditTrail::log('Student', $student->id, 'restored', 'status', 'Archived', 'Enrolled', 'admin', $adminUser?->id);

        return $student->fresh();
    }

    public function changeStatus(Student $student, string $newStatus, ?object $adminUser = null): Student
    {
        if ($student->is_alumni) {
            throw ValidationException::withMessages(['error' => 'Cannot modify Alumni records.']);
        }

        $oldStatus = $student->enrollment_status;
        $student->update(['enrollment_status' => $newStatus]);

        AuditTrail::log('Student', $student->id, 'status_change', 'status', $oldStatus, $newStatus, 'admin', $adminUser?->id);

        return $student->fresh();
    }
}

