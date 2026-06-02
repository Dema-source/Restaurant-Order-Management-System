<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Admin\MenuItem\StoreMenuItemRequest;
use App\Http\Requests\Admin\MenuItem\UpdateMenuItemRequest;
use App\Http\Resources\MenuItemResource;
use App\Services\MenuItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class MenuItemController extends BaseApiController
{
    public function __construct(
        protected MenuItemService $menuItemService
    ) {}

    /**
     * Store a newly created menu item in storage.
     *
     * This method handles file upload for the menu item image before
     * creating the record. The image is stored in the public disk
     * under the 'menu-items' directory.
     *
     * @param StoreMenuItemRequest $request The validated request
     * @return JsonResponse The created menu item resource
     */
    public function store(StoreMenuItemRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Handle file upload
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('menu-items', 'public');
        }

        $menuItem = $this->menuItemService->create($data);

        return $this->createdResponse(
            new MenuItemResource($menuItem)
        );
    }

    /**
     * Update the specified menu item in storage.
     *
     * This method handles file upload for the menu item image and
     * deletes the old image file if a new one is uploaded.
     *
     * @param UpdateMenuItemRequest $request The validated request
     * @param int $id The menu item ID
     * @return JsonResponse The updated menu item resource
     */
    public function update(UpdateMenuItemRequest $request, int $id): JsonResponse
    {
        if (!$this->menuItemService->exists($id)) {
            return $this->notFoundResponse();
        }

        $data = $request->validated();
        $menuItem = $this->menuItemService->findById($id);

        // Handle file upload and delete old image
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($menuItem->image && Storage::disk('public')->exists($menuItem->image)) {
                Storage::disk('public')->delete($menuItem->image);
            }

            // Store new image
            $data['image'] = $request->file('image')->store('menu-items', 'public');
        }

        $this->menuItemService->update($id, $data);
        $menuItem = $this->menuItemService->findById($id);

        return $this->successResponse(
            new MenuItemResource($menuItem),
            'Resource updated successfully'
        );
    }

    /**
     * Remove the specified menu item from storage.
     *
     * This method deletes the associated image file from storage
     * before deleting the menu item record.
     *
     * @param int $id The menu item ID
     * @return JsonResponse No content response
     */
    public function destroy(int $id): JsonResponse
    {
        if (!$this->menuItemService->exists($id)) {
            return $this->notFoundResponse();
        }

        $menuItem = $this->menuItemService->findById($id);

        // Delete associated image file
        if ($menuItem->image && Storage::disk('public')->exists($menuItem->image)) {
            Storage::disk('public')->delete($menuItem->image);
        }

        $this->menuItemService->delete($id);

        return $this->noContentResponse();
    }

    /**
     * Toggle the availability status of the specified menu item.
     *
     * @param int $id The menu item ID
     * @return JsonResponse Success response
     */
    public function toggleAvailable(int $id): JsonResponse
    {
        if (!$this->menuItemService->exists($id)) {
            return $this->notFoundResponse();
        }

        $this->menuItemService->toggleAvailable($id);

        return $this->successResponse(
            null,
            'MenuItem availability toggled successfully'
        );
    }
}
