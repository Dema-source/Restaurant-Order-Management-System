<?php

namespace App\Services;

use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryService extends BaseService
{
    public function __construct(CategoryRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Get paginated categories with optional filters.
     *
     * This method applies search and filter criteria to the categories query
     * and returns paginated results. Supported filters:
     * - search: Search by category name (English and Arabic)
     * - is_active: Filter by active status
     * - created_at_from: Filter by creation date (from)
     * - created_at_to: Filter by creation date (to)
     *
     * @param array $filters The filter criteria
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator Paginated categories
     */
    public function getPaginatedWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getPaginatedWithFilters($filters, $perPage);
    }

    public function getActive(): Collection
    {
        return $this->repository->getActive();
    }

    public function toggleActive(int $id): bool
    {
        return $this->repository->toggleActive($id);
    }
}
