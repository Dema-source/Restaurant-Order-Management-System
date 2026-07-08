<?php

namespace App\Services;

use App\Repositories\Contracts\CustomerRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerService extends BaseService
{
    public function __construct(CustomerRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Find a customer by phone number.
     *
     * @param string $phone The phone number
     * @return Model|null The customer or null if not found
     */
    public function findByPhone(string $phone): ?Model
    {
        return $this->repository->findByPhone($phone);
    }

    /**
     * Find or create a customer by phone number.
     *
     * This method searches for a customer by phone number.
     * If found, returns the existing customer.
     * If not found, creates a new customer with the provided data.
     *
     * @param array $data Customer data (phone is required, other fields optional)
     * @return Model The customer (existing or newly created)
     */
    public function findOrCreateByPhone(array $data): Model
    {
        $phone = $data['phone'];

        $customer = $this->repository->findByPhone($phone);

        if ($customer) {
            return $customer;
        }

        return $this->repository->create([
            'name' => $data['name'] ?? null,
            'phone' => $phone,
            'alternate_phone' => $data['alternate_phone'] ?? null,
            'address' => $data['address'] ?? 'Default Address',
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Get paginated customers with optional filters.
     *
     * This method delegates to the repository to retrieve customers
     * with search and filter criteria applied.
     *
     * @param array $filters The filter criteria
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator Paginated customers
     */
    public function getPaginatedWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getPaginatedWithFilters($filters, $perPage);
    }
}
