<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\InventoryMovement\IndexInventoryMovementRequest;
use App\Http\Requests\InventoryMovement\InventoryMovementRequest;
use App\Http\Resources\InventoryMovementResource;
use App\Services\InventoryMovementService;
use Illuminate\Http\JsonResponse;

/**
 * Inventory movement controller for admin and cashier access.
 *
 * This controller provides inventory management operations including
 * restock, waste, and adjustment for authorized users (admin and cashier).
 */
class InventoryMovementController extends BaseApiController
{
    public function __construct(
        protected InventoryMovementService $inventoryMovementService
    ) {}

    /**
     * Display a listing of inventory movements.
     *
     * This endpoint returns paginated inventory movements for admin and cashier.
     * Supports filtering by search, type, reason, menu item, order, and date range.
     *
     * @param IndexInventoryMovementRequest $request The validated request
     * @return JsonResponse Paginated list of inventory movements
     */
    public function index(IndexInventoryMovementRequest $request): JsonResponse
    {
        $data = $this->inventoryMovementService->getPaginatedWithFilters(
            filters: $request->validated(),
            perPage: $request->integer('per_page', 15)
        );

        return $this->paginatedResponse($data);
    }

    /**
     * Display the specified inventory movement.
     *
     * This endpoint returns a single inventory movement by ID.
     *
     * @param int $id The inventory movement ID
     * @return JsonResponse The inventory movement resource
     */
    public function show(int $id): JsonResponse
    {
        $inventoryMovement = $this->inventoryMovementService->findById($id);

        if (!$inventoryMovement) {
            return $this->notFoundResponse();
        }

        return $this->successResponse(
            new InventoryMovementResource($inventoryMovement)
        );
    }

    /**
     * Record a restock inventory movement.
     *
     * This endpoint creates a restock movement to increase inventory quantity.
     * Only admin and cashier users can access this endpoint.
     *
     * @param InventoryMovementRequest $request The validated request
     * @return JsonResponse The created inventory movement
     */
    public function restock(InventoryMovementRequest $request): JsonResponse
    {
        $inventoryMovement = $this->inventoryMovementService->restock(
            $request->validated()
        );

        return $this->createdResponse(
            new InventoryMovementResource($inventoryMovement)
        );
    }

    /**
     * Record a waste inventory movement.
     *
     * This endpoint creates a waste movement to decrease inventory quantity.
     * Only admin and cashier users can access this endpoint.
     *
     * @param InventoryMovementRequest $request The validated request
     * @return JsonResponse The created inventory movement
     */
    public function waste(InventoryMovementRequest $request): JsonResponse
    {
        $inventoryMovement = $this->inventoryMovementService->waste(
            $request->validated()
        );

        return $this->createdResponse(
            new InventoryMovementResource($inventoryMovement)
        );
    }

    /**
     * Record an adjustment inventory movement.
     *
     * This endpoint creates an adjustment movement to correct inventory discrepancies.
     * Only admin and cashier users can access this endpoint.
     *
     * @param InventoryMovementRequest $request The validated request
     * @return JsonResponse The created inventory movement
     */
    public function adjustment(InventoryMovementRequest $request): JsonResponse
    {
        $inventoryMovement = $this->inventoryMovementService->adjustment(
            $request->validated()
        );

        return $this->createdResponse(
            new InventoryMovementResource($inventoryMovement)
        );
    }

    /**
     * Get current stock level for a menu item.
     *
     * This endpoint returns the current stock level for a specific menu item.
     * Only admin and cashier users can access this endpoint.
     *
     * @param int $menuItemId The menu item ID
     * @return JsonResponse The stock level
     */
    public function stockLevel(int $menuItemId): JsonResponse
    {
        $stockLevel = $this->inventoryMovementService->getStockLevel($menuItemId);

        return $this->successResponse([
            'menu_item_id' => $menuItemId,
            'stock_level' => $stockLevel,
        ]);
    }

    /**
     * Check if menu item has sufficient stock.
     *
     * This endpoint checks if the current stock level is sufficient for the required quantity.
     * Only admin and cashier users can access this endpoint.
     *
     * @param int $menuItemId The menu item ID
     * @return JsonResponse The availability status
     */
    public function checkAvailability(int $menuItemId): JsonResponse
    {
        $requiredQuantity = request()->integer('quantity', 1);
        $isAvailable = $this->inventoryMovementService->checkAvailability($menuItemId, $requiredQuantity);

        return $this->successResponse([
            'menu_item_id' => $menuItemId,
            'required_quantity' => $requiredQuantity,
            'is_available' => $isAvailable,
        ]);
    }

    /**
     * Get inventory movements by date range.
     *
     * This endpoint returns paginated inventory movements filtered by date range.
     * Only admin and cashier users can access this endpoint.
     *
     * @return JsonResponse Paginated inventory movements
     */
    public function movementsByDateRange(): JsonResponse
    {
        $data = $this->inventoryMovementService->getMovementsByDateRange(
            from: request()->input('from'),
            to: request()->input('to'),
            perPage: request()->integer('per_page', 15)
        );

        return $this->paginatedResponse($data);
    }

    /**
     * Get low stock items.
     *
     * This endpoint returns menu items with stock levels below the specified threshold.
     * Only admin and cashier users can access this endpoint.
     *
     * @return JsonResponse Low stock items
     */
    public function lowStockItems(): JsonResponse
    {
        $threshold = request()->integer('threshold', 10);
        $items = $this->inventoryMovementService->getLowStockItems($threshold);

        return $this->successResponse([
            'threshold' => $threshold,
            'items' => $items,
        ]);
    }

    /**
     * Get waste report by date range.
     *
     * This endpoint returns all waste movements filtered by date range.
     * Only admin and cashier users can access this endpoint.
     *
     * @return JsonResponse Waste report
     */
    public function wasteReport(): JsonResponse
    {
        $waste = $this->inventoryMovementService->getWasteReport(
            from: request()->input('from'),
            to: request()->input('to')
        );

        return $this->successResponse([
            'waste_movements' => InventoryMovementResource::collection($waste),
        ]);
    }
}
