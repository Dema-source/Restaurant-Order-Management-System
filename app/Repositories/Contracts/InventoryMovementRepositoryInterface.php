<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface InventoryMovementRepositoryInterface extends RepositoryInterface
{
    /**
     * Get paginated inventory movements with optional filters.
     *
     * @param array $filters The filter criteria
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator Paginated inventory movements
     */
    public function getPaginatedWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;

    /**
     * Record a restock inventory movement.
     *
     * @param array $data The restock data
     * @return Model The created inventory movement
     */
    public function restock(array $data): Model;

    /**
     * Record a waste inventory movement.
     *
     * @param array $data The waste data
     * @return Model The created inventory movement
     */
    public function waste(array $data): Model;

    /**
     * Record an adjustment inventory movement.
     *
     * @param array $data The adjustment data
     * @return Model The created inventory movement
     */
    public function adjustment(array $data): Model;

    /**
     * Get current stock level for a menu item.
     *
     * @param int $menuItemId The menu item ID
     * @return int The current stock level
     */
    public function getStockLevel(int $menuItemId): int;

    /**
     * Check if menu item has sufficient stock.
     *
     * @param int $menuItemId The menu item ID
     * @param int $requiredQuantity The required quantity
     * @return bool True if available, false otherwise
     */
    public function checkAvailability(int $menuItemId, int $requiredQuantity): bool;

    /**
     * Get inventory movements by date range.
     *
     * @param string|null $from Start date
     * @param string|null $to End date
     * @param int $perPage Items per page
     * @return LengthAwarePaginator Paginated movements
     */
    public function getMovementsByDateRange(?string $from = null, ?string $to = null, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get low stock items.
     *
     * @param int $threshold The low stock threshold
     * @return \Illuminate\Support\Collection Collection of low stock items
     */
    public function getLowStockItems(int $threshold = 10): \Illuminate\Support\Collection;

    /**
     * Get waste report by date range.
     *
     * @param string|null $from Start date
     * @param string|null $to End date
     * @return Collection Collection of waste movements
     */
    public function getWasteReport(?string $from = null, ?string $to = null): Collection;

    /**
     * Reserve stock for an order.
     *
     * This method reserves stock temporarily when an order is created,
     * preventing race conditions when multiple users try to order the same item.
     *
     * @param int $menuItemId The menu item ID
     * @param int $orderId The order ID
     * @param int $quantity The quantity to reserve
     * @return Model The created inventory movement
     */
    public function reserveStock(int $menuItemId, int $orderId, int $quantity): Model;

    /**
     * Release reserved stock for an order.
     *
     * This method releases reserved stock when an order is cancelled,
     * making the stock available again.
     *
     * @param int $orderId The order ID
     * @return bool True if successful, false otherwise
     */
    public function releaseReservedStock(int $orderId): bool;

    /**
     * Confirm reserved stock for an order.
     *
     * This method confirms the reserved stock when an order is completed,
     * converting the reservation to a permanent stock movement.
     *
     * @param int $orderId The order ID
     * @return bool True if successful, false otherwise
     */
    public function confirmReservedStock(int $orderId): bool;

    /**
     * Deduct stock for an order item.
     *
     * This method creates an inventory movement for stock deduction
     * when an order is created.
     *
     * @param int $menuItemId The menu item ID
     * @param int $orderId The order ID
     * @param int $quantity The quantity to deduct
     * @return Model The created inventory movement
     */
    public function deductForOrder(int $menuItemId, int $orderId, int $quantity): Model;
}
