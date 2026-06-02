<?php

namespace App\Repositories\Contracts;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface MenuItemRepositoryInterface extends RepositoryInterface
{
    public function getPaginatedWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;

    public function getAvailable(): Collection;

    public function toggleAvailable(int $id): bool;
}
