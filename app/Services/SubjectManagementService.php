<?php

namespace App\Services;

use App\Models\Subject;
use App\Repositories\Contracts\SubjectRepositoryInterface;

class SubjectManagementService
{
    public function __construct(private readonly SubjectRepositoryInterface $subjects)
    {
    }

    public function getAdminSubjects(bool $archived): array
    {
        return [
            'subjects' => $this->subjects->paginateForAdmin($archived, 10),
            'viewArchived' => $archived,
        ];
    }

    public function create(array $validated): Subject
    {
        return $this->subjects->create($validated);
    }

    public function update(Subject $subject, array $validated): Subject
    {
        return $this->subjects->update($subject, $validated);
    }

    public function archive(Subject $subject): void
    {
        $this->subjects->archive($subject);
    }

    public function restore(int $id): Subject
    {
        return $this->subjects->restoreById($id);
    }
}
