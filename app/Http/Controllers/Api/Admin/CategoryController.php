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

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->create($request->validated());

        return $this->createdResponse(
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

    public function toggleActive(int $id): JsonResponse
    {
        if (!$this->categoryService->exists($id)) {
            return $this->notFoundResponse();
        }

        $this->categoryService->toggleActive($id);

        return $this->successResponse(
            null,
            'Category status toggled successfully'
        );
    }
}
