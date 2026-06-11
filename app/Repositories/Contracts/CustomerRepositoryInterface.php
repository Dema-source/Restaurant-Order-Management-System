<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface CustomerRepositoryInterface extends RepositoryInterface
{
    /**
     * Find a customer by phone number.
     *
     * @param string $phone The phone number
     * @return Model|null The customer or null if not found
     */
    public function findByPhone(string $phone): ?Model;

    /**
     * Find a customer by phone number or fail.
     *
     * @param string $phone The phone number
     * @return Model The customer
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findByPhoneOrFail(string $phone): Model;

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
    public function getPaginatedWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;
}
