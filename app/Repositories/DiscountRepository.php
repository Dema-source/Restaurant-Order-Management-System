<?php

namespace App\Repositories;

use App\Models\Discount;
use App\Repositories\Contracts\DiscountRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DiscountRepository extends BaseRepository implements DiscountRepositoryInterface
{
    public function __construct(Discount $model)
    {
        parent::__construct($model);
    }

    /**
     * Get paginated discounts with filters.
     *
     * This method applies search, active status, and date range filters
     * to the discount query and returns paginated results.
     *
     * @param array $filters The filter criteria
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator Paginated results
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

    /**
     * Toggle the active status of a discount.
     *
     * @param int $id The discount ID
     * @return bool True if successful, false otherwise
     */
    public function toggleActive(int $id): bool
    {
        $discount = $this->model->findOrFail($id);
        return $discount->update(['is_active' => !$discount->is_active]);
    }
}
