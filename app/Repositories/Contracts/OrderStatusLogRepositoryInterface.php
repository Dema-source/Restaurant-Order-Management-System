<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;

interface OrderStatusLogRepositoryInterface extends RepositoryInterface
{
    /**
     * Log a status change for an order.
     *
     * @param int $orderId The order ID
     * @param string $newStatus The new status
     * @param string|null $oldStatus The old status (null for first status)
     * @param string|null $notes Optional notes
     * @return Model The created status log
     */
    public function logStatusChange(int $orderId, string $newStatus, ?string $oldStatus = null, ?string $notes = null): Model;

    /**
     * Get all status logs for an order.
     *
     * @param int $orderId The order ID
     * @return \Illuminate\Database\Eloquent\Collection Collection of status logs
     */
    public function getLogsByOrder(int $orderId): \Illuminate\Database\Eloquent\Collection;
}
