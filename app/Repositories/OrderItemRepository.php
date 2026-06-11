<?php

namespace App\Repositories;

use App\Models\OrderItem;
use App\Repositories\Contracts\OrderItemRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderItemRepository extends BaseRepository implements OrderItemRepositoryInterface
{
    public function __construct(OrderItem $model)
    {
        parent::__construct($model);
    }

    /**
     * Get paginated order items with optional filters.
     *
     * This method applies search and filter criteria and returns paginated results.
     *
     * @param array $filters The filter criteria
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator Paginated order items
     */
    public function getPaginatedWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Apply order filter if provided
        if (isset($filters['order_id']) && !empty($filters['order_id'])) {
            $query->where('order_id', $filters['order_id']);
        }

        // Apply menu item filter if provided
        if (isset($filters['menu_item_id']) && !empty($filters['menu_item_id'])) {
            $query->where('menu_item_id', $filters['menu_item_id']);
        }

        // Apply date range filter if provided
        if (isset($filters['created_at_from']) && !empty($filters['created_at_from'])) {
            $query->whereDate('created_at', '>=', $filters['created_at_from']);
        }

        if (isset($filters['created_at_to']) && !empty($filters['created_at_to'])) {
            $query->whereDate('created_at', '<=', $filters['created_at_to']);
        }

        return $query->with(['order', 'menuItem'])
            ->latest('created_at')
            ->paginate($perPage);
    }

    /**
     * Get all order items for a specific order.
     *
     * @param int $orderId The order ID
     * @return Collection Collection of order items
     */
    public function getItemsByOrder(int $orderId): Collection
    {
        return $this->model->where('order_id', $orderId)
            ->with(['menuItem'])
            ->get();
    }

    /**
     * Calculate subtotal for an order.
     *
     * This method sums up all subtotals for items in a specific order.
     *
     * @param int $orderId The order ID
     * @return float The subtotal amount
     */
    public function calculateOrderSubtotal(int $orderId): float
    {
        return (float) $this->model->where('order_id', $orderId)
            ->sum('subtotal');
    }

    /**
     * Sync order items for an order within a transaction.
     *
     * This method ensures atomicity when replacing all items for an order.
     * It deletes existing items and creates new ones in a single transaction.
     *
     * @param int $orderId The order ID
     * @param array $items Array of items with menu_item_id, quantity, unit_price, notes
     * @return array The synced order items
     */
    public function syncItemsForOrder(int $orderId, array $items): array
    {
        return DB::transaction(function () use ($orderId, $items) {
            // Delete all existing order items for this order
            $this->model->where('order_id', $orderId)->delete();

            // Create new order items
            $synced = [];
            foreach ($items as $itemData) {
                $subtotal = $itemData['quantity'] * $itemData['unit_price'];
                $orderItem = $this->model->create([
                    'order_id' => $orderId,
                    'menu_item_id' => $itemData['menu_item_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'subtotal' => $subtotal,
                    'notes' => $itemData['notes'] ?? null,
                ]);
                $synced[] = $orderItem;
            }

            return $synced;
        });
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
        return $this->model->where('order_id', $orderId)
            ->where('menu_item_id', $menuItemId)
            ->delete();
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
        return $this->model->where('order_id', $orderId)
            ->where('menu_item_id', $menuItemId)
            ->exists();
    }
}
