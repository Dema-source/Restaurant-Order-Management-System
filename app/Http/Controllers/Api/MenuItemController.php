<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\MenuItemResource;
use App\Services\MenuItemService;
use Illuminate\Http\JsonResponse;

class MenuItemController extends BaseApiController
{
    public function __construct(
        protected MenuItemService $menuItemService
    ) {}

    public function index(): JsonResponse
    {
        $data = $this->menuItemService->getPaginatedWithFilters(
            filters: request()->all(),
            perPage: request()->integer('per_page', 15)
        );

        return $this->paginatedResponse($data);
    }

    public function show(int $id): JsonResponse
    {
        $menuItem = $this->menuItemService->findById($id);

        if (!$menuItem) {
            return $this->notFoundResponse();
        }

        return $this->successResponse(
            new MenuItemResource($menuItem)
        );
    }
}
