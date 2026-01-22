<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface BaseRepositoryInterface
{
    public function all(array $columns = ['*']): Collection;

    public function find(int $id, array $columns = ['*']): ?Model;

    public function findOrFail(int $id, array $columns = ['*']): Model;

    public function create(array $data): Model;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    public function where(string $column, $operator, $value = null): self;

    public function whereIn(string $column, array $values): self;

    public function with(array $relations): self;

    public function withCount(array $relations): self;

    public function orderBy(string $column, string $direction = 'asc'): self;

    public function latest(string $column = 'created_at'): self;

    public function limit(int $limit): self;
}
