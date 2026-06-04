<?php

namespace App\Services;

use App\Repositories\Contracts\InventoryMovementRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class InventoryMovementService extends BaseService
{
    public function __construct(InventoryMovementRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Get paginated inventory movements with optional filters.
     *
     * This method applies search and filter criteria to the inventory movements query
     * and returns paginated results. Supported filters:
     * - search: Search by reason or notes
     * - type: Filter by movement type (in/out)
     * - reason: Filter by reason (order, restock, waste, adjustment)
     * - menu_item_id: Filter by menu item
     * - order_id: Filter by order
     * - created_at_from: Filter by creation date (from)
     * - created_at_to: Filter by creation date (to)
     *
     * @param array $filters The filter criteria
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator Paginated inventory movements
     */
    public function getPaginatedWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getPaginatedWithFilters($filters, $perPage);
    }

    /**
     * Record a restock inventory movement.
     *
     * This method delegates to the repository to create a restock
     * inventory movement record, increasing the available quantity.
     *
     * @param array $data The restock data (menu_item_id, quantity, notes)
     * @return Model The created inventory movement
     */
    public function restock(array $data): Model
    {
        return $this->repository->restock($data);
    }

    /**
     * Record a waste inventory movement.
     *
     * This method delegates to the repository to create a waste
     * inventory movement record, decreasing the available quantity.
     *
     * @param array $data The waste data (menu_item_id, quantity, notes)
     * @return Model The created inventory movement
     */
    public function waste(array $data): Model
    {
        return $this->repository->waste($data);
    }

    /**
     * Record an adjustment inventory movement.
     *
     * This method delegates to the repository to create an adjustment
     * inventory movement record for correcting inventory discrepancies.
     *
     * @param array $data The adjustment data (menu_item_id, quantity, notes)
     * @return Model The created inventory movement
     */
    public function adjustment(array $data): Model
    {
        return $this->repository->adjustment($data);
    }

    /**
     * Get current stock level for a menu item.
     *
     * This method delegates to the repository to calculate the
     * current stock level for a menu item.
     *
     * @param int $menuItemId The menu item ID
     * @return int The current stock level
     */
    public function getStockLevel(int $menuItemId): int
    {
        return $this->repository->getStockLevel($menuItemId);
    }

    /**
     * Check if menu item has sufficient stock.
     *
     * This method delegates to the repository to check if the
     * current stock level is sufficient for the required quantity.
     *
     * @param int $menuItemId The menu item ID
     * @param int $requiredQuantity The required quantity
     * @return bool True if available, false otherwise
     */
    public function checkAvailability(int $menuItemId, int $requiredQuantity): bool
    {
        return $this->repository->checkAvailability($menuItemId, $requiredQuantity);
    }

    /**
     * Get inventory movements by date range.
     *
     * This method delegates to the repository to retrieve paginated
     * inventory movements filtered by date range.
     *
     * @param string|null $from Start date
     * @param string|null $to End date
     * @param int $perPage Items per page
     * @return LengthAwarePaginator Paginated movements
     */
    public function getMovementsByDateRange(?string $from = null, ?string $to = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getMovementsByDateRange($from, $to, $perPage);
    }

    /**
     * Get low stock items.
     *
     * This method delegates to the repository to retrieve menu items
     * with stock levels below the specified threshold.
     *
     * @param int $threshold The low stock threshold
     * @return \Illuminate\Support\Collection Collection of low stock items
     */
    public function getLowStockItems(int $threshold = 10): \Illuminate\Support\Collection
    {
        return $this->repository->getLowStockItems($threshold);
    }

    /**
     * Get waste report by date range.
     *
     * This method delegates to the repository to retrieve all waste
     * movements filtered by date range.
     *
     * @param string|null $from Start date
     * @param string|null $to End date
     * @return Collection Collection of waste movements
     */
    public function getWasteReport(?string $from = null, ?string $to = null): Collection
    {
        return $this->repository->getWasteReport($from, $to);
    }

    /**
     * Reserve stock for an order.
     *
     * This method delegates to the repository to reserve stock temporarily
     * when an order is created, preventing race conditions.
     *
     * @param int $menuItemId The menu item ID
     * @param int $orderId The order ID
     * @param int $quantity The quantity to reserve
     * @return Model The created inventory movement
     * @throws \Exception If insufficient stock
     */
    public function reserveStock(int $menuItemId, int $orderId, int $quantity): Model
    {
        return $this->repository->reserveStock($menuItemId, $orderId, $quantity);
    }

    /**
     * Release reserved stock for an order.
     *
     * This method delegates to the repository to release reserved stock
     * when an order is cancelled, making the stock available again.
     *
     * @param int $orderId The order ID
     * @return bool True if successful, false otherwise
     */
    public function releaseReservedStock(int $orderId): bool
    {
        return $this->repository->releaseReservedStock($orderId);
    }

    /**
     * Confirm reserved stock for an order.
     *
     * This method delegates to the repository to confirm reserved stock
     * when an order is completed, converting the reservation to permanent.
     *
     * @param int $orderId The order ID
     * @return bool True if successful, false otherwise
     */
    public function confirmReservedStock(int $orderId): bool
    {
        return $this->repository->confirmReservedStock($orderId);
    }
}
