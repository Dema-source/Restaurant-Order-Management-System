<?php

namespace App\Repositories;

use App\Models\MenuItem;
use App\Repositories\Contracts\MenuItemRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class MenuItemRepository extends BaseRepository implements MenuItemRepositoryInterface
{
    public function __construct(MenuItem $model)
    {
        parent::__construct($model);
    }

    public function getPaginatedWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Apply search filter if provided
        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Apply active status filter if provided
        if (isset($filters['is_available']) && $filters['is_available'] !== '') {
            $query->where('is_available', $filters['is_available']);
        }

        // Apply date range filter if provided
        $query->dateRange(
            from: $filters['created_at_from'] ?? null,
            to: $filters['created_at_to'] ?? null
        );

        return $query->paginate($perPage);
    }

    public function getAvailable(): Collection
    {
        return $this->model->available()->orderBy('name')->get();
    }

    public function toggleAvailable(int $id): bool
    {
        $menuItem = $this->model->findOrFail($id);
        return $menuItem->update(['is_available' => !$menuItem->is_available]);
    }
}
