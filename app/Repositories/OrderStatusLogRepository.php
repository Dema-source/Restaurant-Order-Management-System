<?php

namespace App\Repositories;

use App\Models\OrderStatusLog;
use App\Repositories\Contracts\OrderStatusLogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderStatusLogRepository extends BaseRepository implements OrderStatusLogRepositoryInterface
{
    public function __construct(OrderStatusLog $model)
    {
        parent::__construct($model);
    }

    /**
     * Log a status change for an order.
     *
     * @param int $orderId The order ID
     * @param string $newStatus The new status
     * @param string|null $oldStatus The old status (null for first status)
     * @param string|null $notes Optional notes
     * @return Model The created status log
     */
    public function logStatusChange(int $orderId, string $newStatus, ?string $oldStatus = null, ?string $notes = null): Model
    {
        return $this->model->create([
            'order_id' => $orderId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'notes' => $notes,
            'created_at' => now(),
        ]);
    }

    /**
     * Get all status logs for an order.
     *
     * @param int $orderId The order ID
     * @return Collection Collection of status logs
     */
    public function getLogsByOrder(int $orderId): Collection
    {
        return $this->model->where('order_id', $orderId)
            ->with(['changedBy'])
            ->latest('created_at')
            ->get();
    }

    /**
     * Get paginated status logs with optional filters.
     *
     * @param array $filters The filter criteria
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator Paginated status logs
     */
    public function getPaginatedWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Apply search filter if provided
        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Apply order filter if provided
        if (isset($filters['order_id']) && !empty($filters['order_id'])) {
            $query->byOrder($filters['order_id']);
        }

        // Apply status filter if provided
        if (isset($filters['status']) && !empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        // Apply date range filter if provided
        $query->dateRange(
            from: $filters['created_at_from'] ?? null,
            to: $filters['created_at_to'] ?? null
        );

        return $query->with(['order', 'changedBy'])
            ->latest('created_at')
            ->paginate($perPage);
    }
}
