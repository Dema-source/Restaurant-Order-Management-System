<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Admin\Discount\DuplicateDiscountRequest;
use App\Http\Requests\Admin\Discount\StoreDiscountRequest;
use App\Http\Requests\Admin\Discount\UpdateDiscountRequest;
use App\Http\Resources\DiscountResource;
use App\Services\DiscountService;
use Illuminate\Http\JsonResponse;

class DiscountController extends BaseApiController
{
    public function __construct(
        protected DiscountService $discountService
    ) {}

    public function store(StoreDiscountRequest $request): JsonResponse
    {
        $discount = $this->discountService->create($request->validated());

        return $this->createdResponse(
            new DiscountResource($discount)
        );
    }

    public function update(UpdateDiscountRequest $request, int $id): JsonResponse
    {
        if (!$this->discountService->exists($id)) {
            return $this->notFoundResponse();
        }

        $this->discountService->update($id, $request->validated());
        $discount = $this->discountService->findById($id);

        return $this->successResponse(
            new DiscountResource($discount),
            'Resource updated successfully'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        if (!$this->discountService->exists($id)) {
            return $this->notFoundResponse();
        }

        $this->discountService->delete($id);

        return $this->noContentResponse();
    }

    /**
     * Duplicate an existing discount.
     *
     * This method creates a new discount based on an existing one,
     * allowing the user to override specific fields like name and code.
     *
     * @param DuplicateDiscountRequest $request The validated request
     * @param int $id The original discount ID
     * @return JsonResponse The newly created discount resource
     */
    public function duplicate(DuplicateDiscountRequest $request, int $id): JsonResponse
    {
        if (!$this->discountService->exists($id)) {
            return $this->notFoundResponse();
        }

        $discount = $this->discountService->duplicate($id, $request->validated());

        return $this->createdResponse(
            new DiscountResource($discount),
            'Discount duplicated successfully'
        );
    }

    /**
     * Toggle the active status of the specified discount.
     *
     * @param int $id The discount ID
     * @return JsonResponse Success response
     */
    public function toggleActive(int $id): JsonResponse
    {
        if (!$this->discountService->exists($id)) {
            return $this->notFoundResponse();
        }

        $this->discountService->toggleActive($id);

        return $this->successResponse(
            null,
            'Discount status toggled successfully'
        );
    }
}
