<?php

namespace App\Services;

use App\Repositories\Contracts\InventoryMovementRepositoryInterface;
use App\Repositories\Contracts\MenuItemRepositoryInterface;
use App\Repositories\Contracts\OrderItemRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\OrderStatusLogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderService extends BaseService
{
    public function __construct(
        OrderRepositoryInterface $repository,
        private CustomerService $customerService,
        private DiscountService $discountService,
        private OrderItemRepositoryInterface $orderItemRepository,
        private InventoryMovementRepositoryInterface $inventoryMovementRepository,
        private OrderStatusLogRepositoryInterface $orderStatusLogRepository,
        private MenuItemRepositoryInterface $menuItemRepository
    ) {
        parent::__construct($repository);
    }

    /**
     * Get paginated orders with optional filters.
     *
     * @param array $filters The filter criteria
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator Paginated orders
     */
    public function getPaginatedWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getPaginatedWithFilters($filters, $perPage);
    }

    /**
     * Get orders by customer.
     *
     * @param int $customerId The customer ID
     * @return Collection Collection of orders
     */
    public function getOrdersByCustomer(int $customerId): Collection
    {
        return $this->repository->getOrdersByCustomer($customerId);
    }

    /**
     * Get orders by status.
     *
     * @param string $status The order status
     * @return Collection Collection of orders
     */
    public function getOrdersByStatus(string $status): Collection
    {
        return $this->repository->getOrdersByStatus($status);
    }

    /**
     * Update order status.
     *
     * @param int $orderId The order ID
     * @param string $status The new status
     * @return bool True if successful
     */
    public function updateOrderStatus(int $orderId, string $status): bool
    {
        return $this->repository->updateOrderStatus($orderId, $status);
    }

    /**
     * Find an order by ID.
     *
     * @param int $id The order ID
     * @return Model|null The order or null if not found
     */
    public function find(int $id): ?Model
    {
        return $this->repository->findById($id);
    }

    /**
     * Update order status with stock restoration on cancellation.
     *
     * This method handles status updates with special logic for cancelled orders:
     * - When status changes to cancelled, stock is restored via inventory movements
     * - Order status log is created for status changes
     *
     * @param int $id The order ID
     * @param array $data The status data (status)
     * @return bool True if successful
     */
    public function updateStatus(int $id, array $data): bool
    {
        return DB::transaction(function () use ($id, $data) {
            $order = $this->repository->findById($id, relations: ['items']);

            if (!$order) {
                return false;
            }

            $newStatus = $data['status'];

            // Check if status is being changed to cancelled
            $isCancelling = $newStatus === 'cancelled' && $order->status !== 'cancelled';

            // Update the order status
            $result = $this->repository->update($id, ['status' => $newStatus]);

            // If order is being cancelled, restore stock
            if ($isCancelling && $result) {
                foreach ($order->items as $item) {
                    $this->inventoryMovementRepository->create([
                        'menu_item_id' => $item->menu_item_id,
                        'order_id' => $order->id,
                        'type' => 'in',
                        'reason' => 'adjustment',
                        'quantity' => $item->quantity,
                        'notes' => 'Stock restored from cancelled order: ' . $order->order_number,
                    ]);
                }
            }

            // Create order status log
            $this->orderStatusLogRepository->create([
                'order_id' => $order->id,
                'old_status' => $order->status,
                'new_status' => $newStatus,
                'changed_by' => auth()->id(),
                'notes' => 'Order status updated',
            ]);

            return $result;
        });
    }

    /**
     * Update an order details (delivery_address, notes).
     *
     * This method handles order details updates without affecting status.
     *
     * @param int $id The order ID
     * @param array $data The update data (delivery_address, notes)
     * @return bool True if successful
     */
    public function update(int $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    /**
     * Create a complete order with automatic discount application.
     *
     * This method handles the entire order creation flow in a single transaction:
     * 1. Find or create customer
     * 2. Check stock availability for all items
     * 3. Generate order number
     * 4. Create order
     * 5. Create order items
     * 6. Calculate subtotal
     * 7. Find and apply best discount automatically
     * 8. Calculate final total
     * 9. Update inventory stock quantities
     * 10. Create inventory movements
     * 11. Create initial order status log
     * 12. Commit transaction
     *
     * @param array $data Order data including customer, items, delivery_address, and notes
     * @return Model The created order
     * @throws \Exception If validation fails or any operation fails
     */
    public function createOrder(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // 1. Find or create customer
            $customer = $this->customerService->findOrCreateByPhone($data['customer']);

            // 2. Check stock availability for all items
            foreach ($data['items'] as $item) {
                if (!$this->inventoryMovementRepository->checkAvailability(
                    $item['menu_item_id'],
                    $item['quantity']
                )) {
                    throw new \Exception("Insufficient stock for menu item");
                }
            }

            // 3. Generate order number
            $orderNumber = $this->repository->generateOrderNumber();

            // 4. Create order with initial values
            $order = $this->repository->create([
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'status' => 'new',
                'subtotal' => 0,
                'discount_amount' => 0,
                'total_amount' => 0,
                'delivery_address' => $data['delivery_address'] ?? 'Pickup at restaurant',
                'notes' => $data['notes'] ?? null,
                'discount_id' => null,
            ]);

            // 5. Create order items and calculate subtotal
            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $menuItem = $this->menuItemRepository->findById($item['menu_item_id']);
                if (!$menuItem) {
                    throw new \Exception("Menu item not found");
                }

                $itemSubtotal = $item['quantity'] * $menuItem->price;
                $subtotal += $itemSubtotal;

                $this->orderItemRepository->create([
                    'order_id' => $order->id,
                    'menu_item_id' => $item['menu_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $menuItem->price,
                    'subtotal' => $itemSubtotal,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            // 6. Update order subtotal
            $order->update(['subtotal' => $subtotal]);

            // 7. Find and apply best discount automatically
            $discountAmount = 0;
            $discountId = null;
            $discountInfo = $this->discountService->findBestDiscount($subtotal);

            if ($discountInfo) {
                $discountAmount = $discountInfo['discount_amount'];
                $discountId = $discountInfo['discount_id'];
            }

            // 8. Calculate final total and update discount_id
            // Cap discount at 50% of subtotal, but don't discard it entirely
            $maxDiscount = $subtotal * 0.5; // Maximum 50% discount
            if ($discountAmount > $maxDiscount) {
                $discountAmount = $maxDiscount;
            }
            $totalAmount = $subtotal - $discountAmount;
            $order->update([
                'discount_id' => $discountId,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
            ]);

            // 9. Create inventory movements
            foreach ($data['items'] as $item) {
                $this->inventoryMovementRepository->create([
                    'menu_item_id' => $item['menu_item_id'],
                    'order_id' => $order->id,
                    'type' => 'out',
                    'reason' => 'order',
                    'quantity' => $item['quantity'],
                    'created_by' => auth()->id(),
                    'notes' => 'Order: ' . $order->order_number,
                ]);
            }

            // 10. Create initial order status log
            $this->orderStatusLogRepository->create([
                'order_id' => $order->id,
                'old_status' => null,
                'new_status' => 'new',
                'changed_by' => auth()->id(),
                'notes' => 'Order created',
            ]);

            // 11. Return fresh order with all relationships
            return $order->fresh()->load(['customer', 'items.menuItem', 'discount']);
        });
    }
}
