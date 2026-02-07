<?php

namespace App\Services;

use App\Repositories\Contracts\AuditTrailRepositoryInterface;

class AuditTrailService
{
    public function __construct(private readonly AuditTrailRepositoryInterface $auditTrails)
    {
    }

    public function list(array $filters): array
    {
        return [
            'trails' => $this->auditTrails->paginateFiltered($filters, 20),
            'actions' => $this->auditTrails->getDistinctActions(),
        ];
    }
}
