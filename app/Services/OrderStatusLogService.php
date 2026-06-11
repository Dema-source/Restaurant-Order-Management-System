<?php

namespace App\Services;

use App\Repositories\Contracts\OrderStatusLogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderStatusLogService extends BaseService
{
    public function __construct(OrderStatusLogRepositoryInterface $repository)
    {
        parent::__construct($repository);
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
        return $this->repository->getPaginatedWithFilters($filters, $perPage);
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
        return $this->repository->logStatusChange($orderId, $newStatus, $oldStatus, $notes);
    }

    /**
     * Get all status logs for an order.
     *
     * @param int $orderId The order ID
     * @return Collection Collection of status logs
     */
    public function getLogsByOrder(int $orderId): Collection
    {
        return $this->repository->getLogsByOrder($orderId);
    }
}
