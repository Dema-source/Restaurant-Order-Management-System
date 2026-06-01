<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Admin\Category\StoreCategoryRequest;
use App\Http\Requests\Admin\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;

class CategoryController extends BaseApiController
{
    public function __construct(
        protected CategoryService $categoryService
    ) {}

    public function index(): JsonResponse
    {
        $data = $this->categoryService->getPaginated(
            perPage: request()->integer('per_page', 15)
        );

        return $this->paginatedResponse($data);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->create($request->validated());

        return $this->createdResponse(
            new CategoryResource($category)
        );
    }

    public function show(int $id): JsonResponse
    {
        $category = $this->categoryService->findById($id);

        if (!$category) {
            return $this->notFoundResponse();
        }

        return $this->successResponse(
            new CategoryResource($category)
        );
    }

    public function update(UpdateCategoryRequest $request, int $id): JsonResponse
    {
        if (!$this->categoryService->exists($id)) {
            return $this->notFoundResponse();
        }

        $this->categoryService->update($id, $request->validated());
        $category = $this->categoryService->findById($id);

        return $this->successResponse(
            new CategoryResource($category),
            'Resource updated successfully'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        if (!$this->categoryService->exists($id)) {
            return $this->notFoundResponse();
        }

        $this->categoryService->delete($id);

        return $this->noContentResponse();
    }
}
