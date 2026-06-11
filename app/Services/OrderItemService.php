<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\Order;
use App\Repositories\Contracts\OrderItemRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderItemService extends BaseService
{
    public function __construct(OrderItemRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Get paginated order items with optional filters.
     *
     * @param array $filters The filter criteria
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator Paginated order items
     */
    public function getPaginatedWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getPaginatedWithFilters($filters, $perPage);
    }

    /**
     * Get all order items for a specific order.
     *
     * @param int $orderId The order ID
     * @return Collection Collection of order items
     */
    public function getItemsByOrder(int $orderId): Collection
    {
        return $this->repository->getItemsByOrder($orderId);
    }

    /**
     * Calculate subtotal for an order.
     *
     * @param int $orderId The order ID
     * @return float The subtotal amount
     */
    public function calculateOrderSubtotal(int $orderId): float
    {
        return $this->repository->calculateOrderSubtotal($orderId);
    }

    /**
     * Sync order items for an order with validation.
     *
     * This method validates all items before syncing and uses
     * a database transaction to ensure atomicity.
     *
     * @param int $orderId The order ID
     * @param array $items Array of items with menu_item_id, quantity, unit_price, notes
     * @return array The synced order items
     * @throws \Exception If validation fails
     */
    public function syncItemsForOrder(int $orderId, array $items): array
    {
        // Validate order exists
        $order = Order::findOrFail($orderId);

        // Validate all items
        foreach ($items as $itemData) {
            $menuItem = MenuItem::findOrFail($itemData['menu_item_id']);

            if (!$menuItem->is_available) {
                throw new \Exception("Menu item {$menuItem->name} is not available");
            }

            if ($itemData['quantity'] <= 0) {
                throw new \Exception('Quantity must be positive');
            }

            if ($itemData['unit_price'] <= 0) {
                throw new \Exception('Unit price must be positive');
            }
        }

        return $this->repository->syncItemsForOrder($orderId, $items);
    }

    /**
     * Remove an item from an order.
     *
     * @param int $orderId The order ID
     * @param int $menuItemId The menu item ID
     * @return int The number of records deleted
     */
    public function removeItemFromOrder(int $orderId, int $menuItemId): int
    {
        return $this->repository->removeItemFromOrder($orderId, $menuItemId);
    }

    /**
     * Check if a menu item is already in an order.
     *
     * @param int $orderId The order ID
     * @param int $menuItemId The menu item ID
     * @return bool True if exists, false otherwise
     */
    public function itemExistsInOrder(int $orderId, int $menuItemId): bool
    {
        return $this->repository->itemExistsInOrder($orderId, $menuItemId);
    }

    /**
     * Add an item to an order and update order subtotal.
     *
     * This method adds an item and updates the order's subtotal field
     * in a single transaction.
     *
     * @param int $orderId The order ID
     * @param int $menuItemId The menu item ID
     * @param int $quantity The quantity
     * @param float $unitPrice The unit price
     * @param string|null $notes Optional notes
     * @return Model The created order item
     * @throws \Exception If validation fails
     */
    public function addItemToOrder(int $orderId, int $menuItemId, int $quantity, float $unitPrice, ?string $notes = null): Model
    {
        return DB::transaction(function () use ($orderId, $menuItemId, $quantity, $unitPrice, $notes) {
            // Validate order exists
            $order = Order::findOrFail($orderId);

            // Validate menu item exists and is available
            $menuItem = MenuItem::findOrFail($menuItemId);
            if (!$menuItem->is_available) {
                throw new \Exception("Menu item {$menuItem->name} is not available");
            }

            // Validate quantity and price
            if ($quantity <= 0) {
                throw new \Exception('Quantity must be positive');
            }

            if ($unitPrice <= 0) {
                throw new \Exception('Unit price must be positive');
            }

            // Check if item already exists in order
            if ($this->repository->itemExistsInOrder($orderId, $menuItemId)) {
                throw new \Exception('Item already exists in order');
            }

            // Calculate subtotal
            $subtotal = $quantity * $unitPrice;

            // Create order item
            $orderItem = $this->repository->create([
                'order_id' => $orderId,
                'menu_item_id' => $menuItemId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
                'notes' => $notes,
            ]);

            // Recalculate order subtotal
            $orderSubtotal = $this->repository->calculateOrderSubtotal($orderId);

            // Update order
            $order->update([
                'subtotal' => $orderSubtotal,
                'total_amount' => $orderSubtotal - $order->discount_amount,
            ]);

            return $orderItem;
        });
    }

    /**
     * Update an item in an order and update order subtotal.
     *
     * This method updates an item and recalculates the order's subtotal
     * in a single transaction.
     *
     * @param int $orderItemId The order item ID
     * @param int $quantity The new quantity
     * @param float $unitPrice The new unit price
     * @param string|null $notes Optional notes
     * @return Model The updated order item
     * @throws \Exception If validation fails
     */
    public function updateOrderItem(int $orderItemId, int $quantity, float $unitPrice, ?string $notes = null): Model
    {
        return DB::transaction(function () use ($orderItemId, $quantity, $unitPrice, $notes) {
            // Find order item
            $orderItem = $this->repository->findOrFail($orderItemId);
            $orderId = $orderItem->order_id;

            // Validate quantity and price
            if ($quantity <= 0) {
                throw new \Exception('Quantity must be positive');
            }

            if ($unitPrice <= 0) {
                throw new \Exception('Unit price must be positive');
            }

            // Calculate new subtotal
            $subtotal = $quantity * $unitPrice;

            // Update order item
            $orderItem->update([
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
                'notes' => $notes,
            ]);

            // Recalculate order subtotal
            $orderSubtotal = $this->repository->calculateOrderSubtotal($orderId);

            // Update order
            $order = Order::findOrFail($orderId);
            $order->update([
                'subtotal' => $orderSubtotal,
                'total_amount' => $orderSubtotal - $order->discount_amount,
            ]);

            return $orderItem->fresh();
        });
    }

    /**
     * Remove an item from an order and update order subtotal.
     *
     * This method removes an item and recalculates the order's subtotal
     * in a single transaction.
     *
     * @param int $orderId The order ID
     * @param int $menuItemId The menu item ID
     * @return Model The updated order
     */
    public function removeItemAndUpdateOrder(int $orderId, int $menuItemId): Model
    {
        return DB::transaction(function () use ($orderId, $menuItemId) {
            // Remove the item
            $this->repository->removeItemFromOrder($orderId, $menuItemId);

            // Recalculate order subtotal
            $orderSubtotal = $this->repository->calculateOrderSubtotal($orderId);

            // Update order
            $order = Order::findOrFail($orderId);
            $order->update([
                'subtotal' => $orderSubtotal,
                'total_amount' => $orderSubtotal - $order->discount_amount,
            ]);

            return $order->fresh();
        });
    }
}
