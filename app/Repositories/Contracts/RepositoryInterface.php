<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface RepositoryInterface
{
    public function all(array $columns = ['*'], array $relations = []): Collection;

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator;

    public function findById(int $id, array $columns = ['*'], array $relations = []): ?Model;

    public function findByField(string $field, mixed $value, array $columns = ['*']): Collection;

    public function create(array $payload): Model;

    public function update(int $id, array $payload): bool;

    public function deleteById(int $id): bool;

    public function exists(int $id): bool;

    public function findByIds(array $ids, array $columns = ['*'], array $relations = []): Collection;

    public function updateMany(array $ids, array $data): int;

    public function search(array $criteria, int $perPage = 15): LengthAwarePaginator;
}
