<?php

namespace App\Services;

use App\Repositories\Contracts\MenuItemRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class MenuItemService extends BaseService
{
    public function __construct(MenuItemRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function getPaginatedWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getPaginatedWithFilters($filters, $perPage);
    }

    public function getAvailable(): Collection
    {
        return $this->repository->getAvailable();
    }

    public function toggleAvailable(int $id): bool
    {
        return $this->repository->toggleAvailable($id);
    }
}
