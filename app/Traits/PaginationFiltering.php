<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Trait for pagination and filtering logic.
 *
 * Usage in controllers:
 *   $query = Student::query();
 *   $this->applyFilters($query, ['status' => 'enrolled', 'section_id' => 1]);
 *   $this->applySearch($query, ['first_name', 'last_name', 'email'], $searchTerm);
 *   $results = $this->paginate($query, $request);
 */
trait PaginationFiltering
{
    /**
     * Default pagination limit.
     */
    protected int $defaultPerPage = 15;

    /**
     * Maximum pagination limit.
     */
    protected int $maxPerPage = 100;

    /**
     * Apply filters to a query builder.
     *
     * @param Builder $query
     * @param array $filters Key-value pairs where key is column and value is filter value
     * @param array $exactMatch Columns that should use exact match (not LIKE)
     * @return Builder
     */
    protected function applyFilters(Builder $query, array $filters, array $exactMatch = []): Builder
    {
        foreach ($filters as $column => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (in_array($column, $exactMatch)) {
                $query->where($column, $value);
            } else {
                $query->where($column, 'LIKE', "%{$value}%");
            }
        }

        return $query;
    }

    /**
     * Apply search across multiple columns.
     *
     * @param Builder $query
     * @param array $columns Columns to search in
     * @param string|null $searchTerm
     * @return Builder
     */
    protected function applySearch(Builder $query, array $columns, ?string $searchTerm): Builder
    {
        if (empty($searchTerm) || empty($columns)) {
            return $query;
        }

        $query->where(function ($q) use ($columns, $searchTerm) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'LIKE', "%{$searchTerm}%");
            }
        });

        return $query;
    }

    /**
     * Apply date range filter.
     *
     * @param Builder $query
     * @param string $column
     * @param string|null $fromDate
     * @param string|null $toDate
     * @return Builder
     */
    protected function applyDateRange(Builder $query, string $column, ?string $fromDate, ?string $toDate): Builder
    {
        if ($fromDate) {
            $query->whereDate($column, '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate($column, '<=', $toDate);
        }

        return $query;
    }

    /**
     * Apply sorting to a query builder.
     *
     * @param Builder $query
     * @param string|null $sortBy Column to sort by
     * @param string $sortOrder Sort direction (asc/desc)
     * @param array $allowedSorts Columns that are allowed for sorting
     * @return Builder
     */
    protected function applySorting(Builder $query, ?string $sortBy, string $sortOrder = 'desc', array $allowedSorts = []): Builder
    {
        if ($sortBy && (empty($allowedSorts) || in_array($sortBy, $allowedSorts))) {
            $direction = in_array(strtolower($sortOrder), ['asc', 'desc']) ? $sortOrder : 'desc';
            $query->orderBy($sortBy, $direction);
        } else {
            $query->latest();
        }

        return $query;
    }

    /**
     * Paginate results from a query builder.
     *
     * @param Builder $query
     * @param Request|null $request
     * @param int|null $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    protected function paginate(Builder $query, ?Request $request = null, ?int $perPage = null)
    {
        $perPage = $this->resolvePerPage($request, $perPage);

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Simple paginate results (for large datasets).
     *
     * @param Builder $query
     * @param Request|null $request
     * @param int|null $perPage
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    protected function simplePaginate(Builder $query, ?Request $request = null, ?int $perPage = null)
    {
        $perPage = $this->resolvePerPage($request, $perPage);

        return $query->simplePaginate($perPage)->withQueryString();
    }

    /**
     * Resolve the per-page value.
     *
     * @param Request|null $request
     * @param int|null $perPage
     * @return int
     */
    protected function resolvePerPage(?Request $request, ?int $perPage): int
    {
        if ($perPage !== null) {
            return min($perPage, $this->maxPerPage);
        }

        $requestedPerPage = $request?->input('per_page', $this->defaultPerPage);

        return min((int) $requestedPerPage, $this->maxPerPage);
    }

    /**
     * Get filter values from request.
     *
     * @param Request $request
     * @param array $filterKeys Keys to extract from request
     * @return array
     */
    protected function getFiltersFromRequest(Request $request, array $filterKeys): array
    {
        $filters = [];

        foreach ($filterKeys as $key) {
            if ($request->has($key) && $request->input($key) !== '') {
                $filters[$key] = $request->input($key);
            }
        }

        return $filters;
    }
}