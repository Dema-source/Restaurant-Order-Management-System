<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Admin\MenuItem\StoreMenuItemRequest;
use App\Http\Requests\Admin\MenuItem\UpdateMenuItemRequest;
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
        $data = $this->menuItemService->getPaginated(
            perPage: request()->integer('per_page', 15)
        );

        return $this->paginatedResponse($data);
    }

    public function store(StoreMenuItemRequest $request): JsonResponse
    {
        $menuItem = $this->menuItemService->create($request->validated());

        return $this->createdResponse(
            new MenuItemResource($menuItem)
        );
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

    public function update(UpdateMenuItemRequest $request, int $id): JsonResponse
    {
        if (!$this->menuItemService->exists($id)) {
            return $this->notFoundResponse();
        }

        $this->menuItemService->update($id, $request->validated());
        $menuItem = $this->menuItemService->findById($id);

        return $this->successResponse(
            new MenuItemResource($menuItem),
            'Resource updated successfully'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        if (!$this->menuItemService->exists($id)) {
            return $this->notFoundResponse();
        }

        $this->menuItemService->delete($id);

        return $this->noContentResponse();
    }
}
