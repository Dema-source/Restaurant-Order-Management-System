<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrderItemRepositoryInterface extends RepositoryInterface
{
    /**
     * Get paginated order items with optional filters.
     *
     * @param array $filters The filter criteria
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator Paginated order items
     */
    public function getPaginatedWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get all order items for a specific order.
     *
     * @param int $orderId The order ID
     * @return Collection Collection of order items
     */
    public function getItemsByOrder(int $orderId): Collection;

    /**
     * Calculate subtotal for an order.
     *
     * @param int $orderId The order ID
     * @return float The subtotal amount
     */
    public function calculateOrderSubtotal(int $orderId): float;

    /**
     * Sync order items for an order within a transaction.
     *
     * @param int $orderId The order ID
     * @param array $items Array of items with menu_item_id, quantity, unit_price, notes
     * @return array The synced order items
     */
    public function syncItemsForOrder(int $orderId, array $items): array;

    /**
     * Remove an item from an order.
     *
     * @param int $orderId The order ID
     * @param int $menuItemId The menu item ID
     * @return int The number of records deleted
     */
    public function removeItemFromOrder(int $orderId, int $menuItemId): int;

    /**
     * Check if a menu item is already in an order.
     *
     * @param int $orderId The order ID
     * @param int $menuItemId The menu item ID
     * @return bool True if exists, false otherwise
     */
    public function itemExistsInOrder(int $orderId, int $menuItemId): bool;
}
