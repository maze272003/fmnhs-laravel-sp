<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface AttendanceRepositoryInterface
{
    public function getTeachersOrdered(): Collection;

    public function getSectionsOrdered(): Collection;

    public function paginateForAdmin(array $filters, int $perPage = 20): LengthAwarePaginator;
}
