<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\OrderStatusLog\IndexOrderStatusLogRequest;
use App\Http\Resources\OrderStatusLogResource;
use App\Services\OrderStatusLogService;
use Illuminate\Http\JsonResponse;

class OrderStatusLogController extends BaseApiController
{
    public function __construct(
        protected OrderStatusLogService $orderStatusLogService
    ) {}

    public function index(IndexOrderStatusLogRequest $request): JsonResponse
    {
        $data = $this->orderStatusLogService->getPaginatedWithFilters(
            filters: $request->validated(),
            perPage: $request->integer('per_page', 15)
        );

        return $this->paginatedResponse($data);
    }

    public function show(int $id): JsonResponse
    {
        $orderStatusLog = $this->orderStatusLogService->findById($id);

        if (!$orderStatusLog) {
            return $this->notFoundResponse();
        }

        return $this->successResponse(
            new OrderStatusLogResource($orderStatusLog)
        );
    }

    public function getLogsByOrder(int $orderId): JsonResponse
    {
        $logs = $this->orderStatusLogService->getLogsByOrder($orderId);

        return $this->successResponse(
            OrderStatusLogResource::collection($logs)
        );
    }
}
