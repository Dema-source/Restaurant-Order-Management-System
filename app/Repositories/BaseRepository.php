<?php

namespace App\Repositories;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements RepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->model->with($relations)->get($columns);
    }

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator
    {
        return $this->model->with($relations)->paginate($perPage, $columns);
    }

    public function findById(int $id, array $columns = ['*'], array $relations = []): ?Model
    {
        return $this->model->select($columns)->with($relations)->find($id);
    }

    public function findByField(string $field, mixed $value, array $columns = ['*']): Collection
    {
        return $this->model->select($columns)->where($field, $value)->get();
    }

    public function create(array $payload): Model
    {
        return $this->model->create($payload);
    }

    public function update(int $id, array $payload): bool
    {
        $model = $this->findById($id);

        if (!$model) {
            return false;
        }

        return $model->update($payload);
    }

    public function deleteById(int $id): bool
    {
        $model = $this->findById($id);

        if (!$model) {
            return false;
        }

        return $model->delete();
    }

    public function exists(int $id): bool
    {
        return $this->model->where('id', $id)->exists();
    }

    public function findByIds(array $ids, array $columns = ['*'], array $relations = []): Collection
    {
        return $this->model->select($columns)->with($relations)->whereIn('id', $ids)->get();
    }

    public function updateMany(array $ids, array $data): int
    {
        return $this->model->whereIn('id', $ids)->update($data);
    }

    public function search(array $criteria, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        foreach ($criteria as $field => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (is_array($value)) {
                $query->whereIn($field, $value);
            } elseif (str_contains($field, '_from')) {
                $actualField = str_replace('_from', '', $field);
                $query->where($actualField, '>=', $value);
            } elseif (str_contains($field, '_to')) {
                $actualField = str_replace('_to', '', $field);
                $query->where($actualField, '<=', $value);
            } elseif (str_contains($field, '_like')) {
                $actualField = str_replace('_like', '', $field);
                $query->where($actualField, 'like', "%{$value}%");
            } else {
                $query->where($field, $value);
            }
        }

        return $query->paginate($perPage);
    }
}
