<?php

namespace App\Repositories;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements BaseRepositoryInterface
{
    public function __construct(protected Model $model) {}

    public function all(array $filters = []): Collection
    {
        return $this->model->newQuery()
            ->when(!empty($filters), fn ($q) => $this->applyFilters($q, $filters))
            ->get();
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->when(!empty($filters), fn ($q) => $this->applyFilters($q, $filters))
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int|string $id): ?Model
    {
        return $this->model->newQuery()->find($id);
    }

    public function create(array $data): Model
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(int|string $id, array $data): Model
    {
        $record = $this->model->newQuery()->findOrFail($id);
        $record->update($data);

        return $record->fresh();
    }

    public function delete(int|string $id): bool
    {
        $record = $this->model->newQuery()->findOrFail($id);

        return $record->delete();
    }
    public function getModel(): Model
    {
    return $this->model;
    }
    /**
     * Override in child repositories to add model-specific filter logic.
     */
    protected function applyFilters($query, array $filters)
    {
        return $query;
    }
    
}
