<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\BaseRepositoryInterface;
use App\Support\Exceptions\RepositoryException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    protected array $withRelations = [];

    protected array $withCountRelations = [];

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(array $columns = ['*']): Collection
    {
        return $this->applyRelations()->get($columns);
    }

    public function find(int $id, array $columns = ['*']): ?Model
    {
        return $this->applyRelations()->find($id, $columns);
    }

    public function findOrFail(int $id, array $columns = ['*']): Model
    {
        $result = $this->applyRelations()->find($id, $columns);

        if (!$result) {
            $modelName = Str::classBasename($this->model);
            throw RepositoryException::modelNotFound($modelName, $id);
        }

        return $result;
    }

    public function create(array $data): Model
    {
        try {
            return $this->model->create($data);
        } catch (\Exception $e) {
            $modelName = Str::classBasename($this->model);
            Log::error("Failed to create {$modelName}", ['error' => $e->getMessage(), 'data' => $data]);
            throw RepositoryException::createFailed($modelName, $e->getMessage());
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            $model = $this->findOrFail($id);
            return $model->update($data);
        } catch (\Exception $e) {
            $modelName = Str::classBasename($this->model);
            Log::error("Failed to update {$modelName} with ID {$id}", ['error' => $e->getMessage(), 'data' => $data]);
            throw RepositoryException::updateFailed($modelName, $id, $e->getMessage());
        }
    }

    public function delete(int $id): bool
    {
        try {
            $model = $this->findOrFail($id);
            return $model->delete();
        } catch (\Exception $e) {
            $modelName = Str::classBasename($this->model);
            Log::error("Failed to delete {$modelName} with ID {$id}", ['error' => $e->getMessage()]);
            throw RepositoryException::deleteFailed($modelName, $id, $e->getMessage());
        }
    }

    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->applyRelations()->paginate($perPage, $columns);
    }

    public function where(string $column, $operator, $value = null): self
    {
        if ($value === null) {
            $this->model = $this->model->where($column, $operator);
        } else {
            $this->model = $this->model->where($column, $operator, $value);
        }

        return $this;
    }

    public function whereIn(string $column, array $values): self
    {
        $this->model = $this->model->whereIn($column, $values);
        return $this;
    }

    public function with(array $relations): self
    {
        $this->withRelations = array_merge($this->withRelations, $relations);
        return $this;
    }

    public function withCount(array $relations): self
    {
        $this->withCountRelations = array_merge($this->withCountRelations, $relations);
        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->model = $this->model->orderBy($column, $direction);
        return $this;
    }

    public function latest(string $column = 'created_at'): self
    {
        $this->model = $this->model->latest($column);
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->model = $this->model->limit($limit);
        return $this;
    }

    protected function applyRelations(): Model
    {
        if (!empty($this->withRelations)) {
            $this->model->load($this->withRelations);
            $this->withRelations = [];
        }

        if (!empty($this->withCountRelations)) {
            $this->model->withCount($this->withCountRelations);
            $this->withCountRelations = [];
        }

        return $this->model;
    }

    protected function resetModel(): void
    {
        $this->model = $this->model->newInstance();
        $this->withRelations = [];
        $this->withCountRelations = [];
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
