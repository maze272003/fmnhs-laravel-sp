<?php

namespace App\Repositories\Eloquent;

use App\Models\AuditTrail;
use App\Repositories\Contracts\AuditTrailRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AuditTrailRepository implements AuditTrailRepositoryInterface
{
    public function paginateFiltered(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = AuditTrail::orderBy('created_at', 'desc');

        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (!empty($filters['user_type'])) {
            $query->where('user_type', $filters['user_type']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'LIKE', '%' . $search . '%')
                    ->orWhere('auditable_type', 'LIKE', '%' . $search . '%')
                    ->orWhere('field', 'LIKE', '%' . $search . '%');
            });
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->paginate($perPage);
    }

    public function getDistinctActions(): Collection
    {
        return AuditTrail::select('action')->distinct()->pluck('action');
    }
}
