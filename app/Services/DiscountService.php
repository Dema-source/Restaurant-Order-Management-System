<?php

namespace App\Services;

use App\Repositories\Contracts\DiscountRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class DiscountService extends BaseService
{
    public function __construct(DiscountRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Get paginated discounts with filters.
     *
     * This method delegates to the repository to retrieve paginated
     * discounts with applied filters for search, active status, and date range.
     *
     * @param array $filters The filter criteria
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator Paginated results
     */
    public function getPaginatedWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getPaginatedWithFilters($filters, $perPage);
    }

    /**
     * Duplicate an existing discount with optional overrides.
     *
     * This method creates a new discount based on an existing one,
     * copying all fields except id, code, and timestamps. The user can
     * override specific fields like name and code.
     *
     * @param int $id The original discount ID
     * @param array $overrides Fields to override (name, code, etc.)
     * @return Model The newly created discount
     */
    public function duplicate(int $id, array $overrides = []): Model
    {
        $original = $this->findById($id);

        if (!$original) {
            throw new \Exception('Original discount not found');
        }

        $data = $original->toArray();

        // Remove fields that should not be copied
        unset($data['id'], $data['code'], $data['created_at'], $data['updated_at'], $data['deleted_at']);

        // Merge with user overrides
        $data = array_merge($data, $overrides);

        return $this->create($data);
    }

    /**
     * Toggle the active status of a discount.
     *
     * @param int $id The discount ID
     * @return bool True if successful, false otherwise
     */
    public function toggleActive(int $id): bool
    {
        return $this->repository->toggleActive($id);
    }
}
