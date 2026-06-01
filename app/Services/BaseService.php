<?php

namespace App\Services;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseService
{
    protected RepositoryInterface $repository;

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAll(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->repository->all($columns, $relations);
    }

    public function getPaginated(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $columns, $relations);
    }

    public function findById(int $id, array $columns = ['*'], array $relations = []): ?Model
    {
        return $this->repository->findById($id, $columns, $relations);
    }

    public function create(array $data): Model
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->deleteById($id);
    }

    public function exists(int $id): bool
    {
        return $this->repository->exists($id);
    }

    public function findByIds(array $ids, array $columns = ['*'], array $relations = []): Collection
    {
        return $this->repository->findByIds($ids, $columns, $relations);
    }

    public function updateMany(array $ids, array $data): int
    {
        return $this->repository->updateMany($ids, $data);
    }

    public function search(array $criteria, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->search($criteria, $perPage);
    }
}
