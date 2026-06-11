<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Order\IndexOrderRequest;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends BaseApiController
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Display a listing of orders with optional filters.
     *
     * @param IndexOrderRequest $request
     * @return JsonResponse
     */
    public function index(IndexOrderRequest $request): JsonResponse
    {
        $data = $this->orderService->getPaginatedWithFilters(
            filters: $request->validated(),
            perPage: $request->integer('per_page', 15)
        );

        return $this->paginatedResponse($data);
    }

    /**
     * Store a newly created order in storage.
     *
     * @param StoreOrderRequest $request
     * @return JsonResponse
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->createOrder($request->validated());

        return $this->createdResponse(
            new OrderResource($order),
            'Order created successfully'
        );
    }

    /**
     * Display the specified order.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $order = $this->orderService->findById($id);

        if (!$order) {
            return $this->notFoundResponse();
        }

        return $this->successResponse(
            new OrderResource($order)
        );
    }

    /**
     * Update the specified order in storage.
     *
     * @param UpdateOrderRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateOrderRequest $request, int $id): JsonResponse
    {
        $order = $this->orderService->find($id);

        if (!$order) {
            return $this->notFoundResponse();
        }

        $this->orderService->update($id, $request->validated());
        $order = $this->orderService->find($id);

        return $this->successResponse(
            new OrderResource($order),
            'Order updated successfully'
        );
    }

    /**
     * Update the status of the specified order.
     *
     * @param UpdateOrderStatusRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateStatus(UpdateOrderStatusRequest $request, int $id): JsonResponse
    {
        $order = $this->orderService->find($id);

        if (!$order) {
            return $this->notFoundResponse();
        }

        $this->orderService->updateStatus($id, $request->validated());
        $order = $this->orderService->find($id);

        return $this->successResponse(
            new OrderResource($order),
            'Order status updated successfully'
        );
    }

    /**
     * Remove the specified order from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        if (!$this->orderService->exists($id)) {
            return $this->notFoundResponse();
        }

        $this->orderService->delete($id);

        return $this->noContentResponse();
    }

    /**
     * Get orders by customer.
     *
     * @param int $customerId
     * @return JsonResponse
     */
    public function getByCustomer(int $customerId): JsonResponse
    {
        $orders = $this->orderService->getOrdersByCustomer($customerId);

        return $this->successResponse(
            OrderResource::collection($orders)
        );
    }

    /**
     * Get orders by status.
     *
     * @param string $status
     * @return JsonResponse
     */
    public function getByStatus(string $status): JsonResponse
    {
        $orders = $this->orderService->getOrdersByStatus($status);

        return $this->successResponse(
            OrderResource::collection($orders)
        );
    }
}
