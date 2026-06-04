<?php

namespace App\Repositories;

use App\Models\InventoryMovement;
use App\Repositories\Contracts\InventoryMovementRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InventoryMovementRepository extends BaseRepository implements InventoryMovementRepositoryInterface
{
    public function __construct(InventoryMovement $model)
    {
        parent::__construct($model);
    }

    /**
     * Get paginated inventory movements with optional filters.
     *
     * This method applies search and filter criteria using model scopes
     * and returns paginated results.
     *
     * @param array $filters The filter criteria
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator Paginated inventory movements
     */
    public function getPaginatedWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Apply search filter if provided
        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Apply type filter if provided
        if (isset($filters['type']) && !empty($filters['type'])) {
            $query->byType($filters['type']);
        }

        // Apply reason filter if provided
        if (isset($filters['reason']) && !empty($filters['reason'])) {
            $query->byReason($filters['reason']);
        }

        // Apply menu item filter if provided
        if (isset($filters['menu_item_id']) && !empty($filters['menu_item_id'])) {
            $query->byMenuItem($filters['menu_item_id']);
        }

        // Apply order filter if provided
        if (isset($filters['order_id']) && !empty($filters['order_id'])) {
            $query->byOrder($filters['order_id']);
        }

        // Apply date range filter if provided
        if (isset($filters['created_at_from']) && !empty($filters['created_at_from'])) {
            $query->whereDate('created_at', '>=', $filters['created_at_from']);
        }

        if (isset($filters['created_at_to']) && !empty($filters['created_at_to'])) {
            $query->whereDate('created_at', '<=', $filters['created_at_to']);
        }

        return $query->with(['menuItem', 'order', 'createdBy'])
            ->latest('created_at')
            ->paginate($perPage);
    }

    /**
     * Record a restock inventory movement.
     *
     * This method creates an inventory movement record for restocking
     * menu items, increasing the available quantity.
     *
     * @param array $data The restock data (menu_item_id, quantity, notes)
     * @return Model The created inventory movement
     */
    public function restock(array $data): Model
    {
        return $this->model->create([
            'menu_item_id' => $data['menu_item_id'],
            'order_id' => null,
            'type' => 'in',
            'quantity' => $data['quantity'],
            'reason' => 'restock',
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Record a waste inventory movement.
     *
     * This method creates an inventory movement record for wasted
     * menu items, decreasing the available quantity.
     *
     * @param array $data The waste data (menu_item_id, quantity, notes)
     * @return Model The created inventory movement
     */
    public function waste(array $data): Model
    {
        return $this->model->create([
            'menu_item_id' => $data['menu_item_id'],
            'order_id' => null,
            'type' => 'out',
            'quantity' => $data['quantity'],
            'reason' => 'waste',
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Record an adjustment inventory movement.
     *
     * This method creates an inventory movement record for manual
     * adjustments to correct inventory discrepancies.
     *
     * @param array $data The adjustment data (menu_item_id, quantity, notes)
     * @return Model The created inventory movement
     */
    public function adjustment(array $data): Model
    {
        return $this->model->create([
            'menu_item_id' => $data['menu_item_id'],
            'order_id' => null,
            'type' => $data['quantity'] >= 0 ? 'in' : 'out',
            'quantity' => abs($data['quantity']),
            'reason' => 'adjustment',
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Get current stock level for a menu item.
     *
     * This method calculates the current stock level by summing
     * all 'in' movements and subtracting all 'out' movements.
     *
     * @param int $menuItemId The menu item ID
     * @return int The current stock level
     */
    public function getStockLevel(int $menuItemId): int
    {
        $in = $this->model->where('menu_item_id', $menuItemId)
            ->where('type', 'in')
            ->sum('quantity');

        $out = $this->model->where('menu_item_id', $menuItemId)
            ->where('type', 'out')
            ->sum('quantity');

        return $in - $out;
    }

    /**
     * Check if menu item has sufficient stock.
     *
     * This method checks if the current stock level is sufficient
     * for the required quantity.
     *
     * @param int $menuItemId The menu item ID
     * @param int $requiredQuantity The required quantity
     * @return bool True if available, false otherwise
     */
    public function checkAvailability(int $menuItemId, int $requiredQuantity): bool
    {
        return $this->getStockLevel($menuItemId) >= $requiredQuantity;
    }

    /**
     * Get inventory movements by date range.
     *
     * This method returns paginated inventory movements filtered
     * by creation date range.
     *
     * @param string|null $from Start date
     * @param string|null $to End date
     * @param int $perPage Items per page
     * @return LengthAwarePaginator Paginated movements
     */
    public function getMovementsByDateRange(?string $from = null, ?string $to = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query->with(['menuItem', 'order', 'createdBy'])
            ->latest('created_at')
            ->paginate($perPage);
    }

    /**
     * Get low stock items.
     *
     * This method returns menu items with stock levels below
     * the specified threshold.
     *
     * @param int $threshold The low stock threshold
     * @return \Illuminate\Support\Collection Collection of low stock items
     */
    public function getLowStockItems(int $threshold = 10): \Illuminate\Support\Collection
    {
        // Get all menu items with their stock levels
        $menuItems = \App\Models\MenuItem::all();

        return $menuItems->map(function ($item) use ($threshold) {
            $stockLevel = $this->getStockLevel($item->id);
            return [
                'menu_item' => $item,
                'stock_level' => $stockLevel,
                'is_low_stock' => $stockLevel < $threshold,
            ];
        })->filter(function ($item) {
            return $item['is_low_stock'];
        });
    }

    /**
     * Get waste report by date range.
     *
     * This method returns all waste movements filtered by date range.
     *
     * @param string|null $from Start date
     * @param string|null $to End date
     * @return Collection Collection of waste movements
     */
    public function getWasteReport(?string $from = null, ?string $to = null): Collection
    {
        $query = $this->model->where('reason', 'waste');

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query->with(['menuItem', 'createdBy'])
            ->latest('created_at')
            ->get();
    }

    /**
     * Reserve stock for an order.
     *
     * This method reserves stock temporarily when an order is created,
     * preventing race conditions when multiple users try to order the same item.
     * Uses database transaction to ensure atomicity.
     *
     * @param int $menuItemId The menu item ID
     * @param int $orderId The order ID
     * @param int $quantity The quantity to reserve
     * @return Model The created inventory movement
     * @throws \Exception If insufficient stock
     */
    public function reserveStock(int $menuItemId, int $orderId, int $quantity): Model
    {
        return DB::transaction(function () use ($menuItemId, $orderId, $quantity) {
            // Check availability within transaction
            if (!$this->checkAvailability($menuItemId, $quantity)) {
                throw new \Exception('Insufficient stock available');
            }

            // Create reservation movement
            return $this->model->create([
                'menu_item_id' => $menuItemId,
                'order_id' => $orderId,
                'type' => 'out',
                'quantity' => $quantity,
                'reason' => 'order',
                'notes' => 'Stock reserved for order',
            ]);
        });
    }

    /**
     * Release reserved stock for an order.
     *
     * This method releases reserved stock when an order is cancelled,
     * making the stock available again. Uses database transaction.
     *
     * @param int $orderId The order ID
     * @return bool True if successful, false otherwise
     */
    public function releaseReservedStock(int $orderId): bool
    {
        return DB::transaction(function () use ($orderId) {
            $movements = $this->model->where('order_id', $orderId)
                ->where('reason', 'order')
                ->get();

            foreach ($movements as $movement) {
                // Create a compensating movement to restore stock
                $this->model->create([
                    'menu_item_id' => $movement->menu_item_id,
                    'order_id' => $orderId,
                    'type' => 'in',
                    'quantity' => $movement->quantity,
                    'reason' => 'adjustment',
                    'notes' => 'Stock released from cancelled order',
                ]);
            }

            return true;
        });
    }

    /**
     * Confirm reserved stock for an order.
     *
     * This method confirms the reserved stock when an order is completed,
     * converting the reservation to a permanent stock movement.
     * The reservation is already recorded as 'out' movement, so this
     * just updates the notes to reflect completion.
     *
     * @param int $orderId The order ID
     * @return bool True if successful, false otherwise
     */
    public function confirmReservedStock(int $orderId): bool
    {
        return DB::transaction(function () use ($orderId) {
            $this->model->where('order_id', $orderId)
                ->where('reason', 'order')
                ->update([
                    'notes' => 'Stock confirmed for completed order',
                ]);

            return true;
        });
    }
}
