<?php

namespace App\Repositories;

use App\Models\OrderStatusLog;
use App\Repositories\Contracts\OrderStatusLogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

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
}
