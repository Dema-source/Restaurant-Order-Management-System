<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    /**
     * Get paginated categories with optional filters.
     *
     * This method applies search and filter criteria using model scopes
     * and returns paginated results.
     *
     * @param array $filters The filter criteria
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator Paginated categories
     */
    public function getPaginatedWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Apply search filter if provided
        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Apply active status filter if provided
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', $filters['is_active']);
        }

        // Apply date range filter if provided
        $query->dateRange(
            from: $filters['created_at_from'] ?? null,
            to: $filters['created_at_to'] ?? null
        );

        return $query->paginate($perPage);
    }

    public function getActive(): Collection
    {
        return $this->model->active()->orderBy('name')->get();
    }

    public function toggleActive(int $id): bool
    {
        $category = $this->model->findOrFail($id);
        return $category->update(['is_active' => !$category->is_active]);
    }
}
