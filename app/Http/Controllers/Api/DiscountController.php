<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Discount\IndexDiscountRequest;
use App\Http\Resources\DiscountResource;
use App\Services\DiscountService;
use Illuminate\Http\JsonResponse;

/**
 * General discount controller for all authenticated users.
 *
 * This controller provides read-only access to discounts for all roles.
 * Admin-specific operations (create, update, delete, toggle, duplicate)
 * are handled by the Admin\DiscountController.
 */
class DiscountController extends BaseApiController
{
    public function __construct(
        protected DiscountService $discountService
    ) {}

    /**
     * Display a listing of discounts.
     *
     * This endpoint returns all active discounts for all authenticated users.
     * Non-admin users will only see active discounts due to the global scope.
     * Supports filtering by search, active status, and date range.
     *
     * @param IndexDiscountRequest $request The validated request
     * @return JsonResponse Paginated list of discounts
     */
    public function index(IndexDiscountRequest $request): JsonResponse
    {
        $data = $this->discountService->getPaginatedWithFilters(
            filters: $request->validated(),
            perPage: $request->integer('per_page', 15)
        );

        return $this->paginatedResponse($data);
    }

    /**
     * Display the specified discount.
     *
     * This endpoint returns a single discount by ID.
     * Non-admin users will not see inactive discounts due to the global scope.
     *
     * @param int $id The discount ID
     * @return JsonResponse The discount resource
     */
    public function show(int $id): JsonResponse
    {
        $discount = $this->discountService->findById($id);

        if (!$discount) {
            return $this->notFoundResponse();
        }

        return $this->successResponse(
            new DiscountResource($discount)
        );
    }
}
