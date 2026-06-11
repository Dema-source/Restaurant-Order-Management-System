<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Customer\IndexCustomerRequest;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;

class CustomerController extends BaseApiController
{
    public function __construct(
        protected CustomerService $customerService
    ) {}

    public function index(IndexCustomerRequest $request): JsonResponse
    {
        $data = $this->customerService->getPaginatedWithFilters(
            filters: $request->validated(),
            perPage: $request->integer('per_page', 15)
        );

        return $this->paginatedResponse($data);
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->customerService->create($request->validated());

        return $this->createdResponse(
            new CustomerResource($customer)
        );
    }

    public function show(int $id): JsonResponse
    {
        $customer = $this->customerService->findById($id);

        if (!$customer) {
            return $this->notFoundResponse();
        }

        return $this->successResponse(
            new CustomerResource($customer)
        );
    }

    public function update(UpdateCustomerRequest $request, int $id): JsonResponse
    {
        if (!$this->customerService->exists($id)) {
            return $this->notFoundResponse();
        }

        $this->customerService->update($id, $request->validated());
        $customer = $this->customerService->findById($id);

        return $this->successResponse(
            new CustomerResource($customer),
            'Resource updated successfully'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        if (!$this->customerService->exists($id)) {
            return $this->notFoundResponse();
        }

        $this->customerService->delete($id);

        return $this->noContentResponse();
    }

    /**
     * Find customer by phone number.
     *
     * @param string $phone
     * @return JsonResponse
     */
    public function findByPhone(string $phone): JsonResponse
    {
        $customer = $this->customerService->findByPhone($phone);

        if (!$customer) {
            return $this->notFoundResponse();
        }

        return $this->successResponse(
            new CustomerResource($customer)
        );
    }
}
