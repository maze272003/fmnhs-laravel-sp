<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface AuditTrailRepositoryInterface
{
    public function paginateFiltered(array $filters, int $perPage = 20): LengthAwarePaginator;

    public function getDistinctActions(): Collection;
}
