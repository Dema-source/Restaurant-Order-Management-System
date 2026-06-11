<?php

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    /**
     * Generate a unique order number.
     *
     * @return string The generated order number (e.g., ORD-000001)
     */
    public function generateOrderNumber(): string
    {
        $lastOrder = $this->model->latest('id')->first();
        $lastId = $lastOrder ? $lastOrder->id : 0;
        $nextId = $lastId + 1;

        return 'ORD-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get paginated orders with optional filters.
     *
     * @param array $filters The filter criteria
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator Paginated orders
     */
    public function getPaginatedWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Apply search filter if provided
        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Apply status filter if provided
        if (isset($filters['status']) && !empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        // Apply customer filter if provided
        if (isset($filters['customer_id']) && !empty($filters['customer_id'])) {
            $query->byCustomer($filters['customer_id']);
        }

        // Apply date range filter if provided
        if (isset($filters['created_at_from']) && !empty($filters['created_at_from'])) {
            $query->whereDate('created_at', '>=', $filters['created_at_from']);
        }

        if (isset($filters['created_at_to']) && !empty($filters['created_at_to'])) {
            $query->whereDate('created_at', '<=', $filters['created_at_to']);
        }

        return $query->with(['customer', 'items.menuItem', 'discount'])
            ->latest('created_at')
            ->paginate($perPage);
    }

    /**
     * Get orders by customer.
     *
     * @param int $customerId The customer ID
     * @return Collection Collection of orders
     */
    public function getOrdersByCustomer(int $customerId): Collection
    {
        return $this->model->where('customer_id', $customerId)
            ->with(['items.menuItem', 'discount'])
            ->latest('created_at')
            ->get();
    }

    /**
     * Get orders by status.
     *
     * @param string $status The order status
     * @return Collection Collection of orders
     */
    public function getOrdersByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)
            ->with(['customer', 'items.menuItem', 'discount'])
            ->latest('created_at')
            ->get();
    }

    /**
     * Update order status.
     *
     * @param int $orderId The order ID
     * @param string $status The new status
     * @return bool True if successful
     */
    public function updateOrderStatus(int $orderId, string $status): bool
    {
        return $this->model->where('id', $orderId)->update(['status' => $status]);
    }
}
