<?php

namespace App\Http\Controllers\Api;

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
        $data = $this->categoryService->getPaginatedWithFilters(
            filters: request()->all(),
            perPage: request()->integer('per_page', 15)
        );

        return $this->paginatedResponse($data);
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
}
