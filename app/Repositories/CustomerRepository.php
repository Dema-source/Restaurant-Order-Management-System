<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerRepository extends BaseRepository implements CustomerRepositoryInterface
{
    public function __construct(Customer $model)
    {
        parent::__construct($model);
    }

    /**
     * Find a customer by phone number.
     *
     * @param string $phone The phone number
     * @return Model|null The customer or null if not found
     */
    public function findByPhone(string $phone): ?Model
    {
        return $this->model->where('phone', $phone)->first();
    }

    /**
     * Find a customer by phone number or fail.
     *
     * @param string $phone The phone number
     * @return Model The customer
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findByPhoneOrFail(string $phone): Model
    {
        return $this->model->where('phone', $phone)->firstOrFail();
    }

    /**
     * Get paginated customers with optional filters.
     *
     * This method applies search and filter criteria using model scopes
     * and returns paginated results.
     *
     * @param array $filters The filter criteria
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator Paginated customers
     */
    public function getPaginatedWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Apply search filter if provided
        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Apply date range filter if provided
        $query->dateRange(
            from: $filters['created_at_from'] ?? null,
            to: $filters['created_at_to'] ?? null
        );

        return $query->paginate($perPage);
    }
}
