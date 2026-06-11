<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface extends RepositoryInterface
{
    /**
     * Generate a unique order number.
     *
     * @return string The generated order number (e.g., ORD-000001)
     */
    public function generateOrderNumber(): string;

    /**
     * Get paginated orders with optional filters.
     *
     * @param array $filters The filter criteria
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator Paginated orders
     */
    public function getPaginatedWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get orders by customer.
     *
     * @param int $customerId The customer ID
     * @return Collection Collection of orders
     */
    public function getOrdersByCustomer(int $customerId): Collection;

    /**
     * Get orders by status.
     *
     * @param string $status The order status
     * @return Collection Collection of orders
     */
    public function getOrdersByStatus(string $status): Collection;

    /**
     * Update order status.
     *
     * @param int $orderId The order ID
     * @param string $status The new status
     * @return bool True if successful
     */
    public function updateOrderStatus(int $orderId, string $status): bool;
}
