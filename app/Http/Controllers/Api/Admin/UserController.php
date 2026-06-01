<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends BaseApiController
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function index(): JsonResponse
    {
        $data = $this->userService->getPaginated(
            perPage: request()->integer('per_page', 15)
        );

        return $this->paginatedResponse($data);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->validated());

        return $this->createdResponse(
            new UserResource($user)
        );
    }

    public function show(int $id): JsonResponse
    {
        $user = $this->userService->findById($id);

        if (!$user) {
            return $this->notFoundResponse();
        }

        return $this->successResponse(
            new UserResource($user)
        );
    }

    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        if (!$this->userService->exists($id)) {
            return $this->notFoundResponse();
        }

        $this->userService->update($id, $request->validated());
        $user = $this->userService->findById($id);

        return $this->successResponse(
            new UserResource($user),
            'Resource updated successfully'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        if (!$this->userService->exists($id)) {
            return $this->notFoundResponse();
        }

        $this->userService->delete($id);

        return $this->noContentResponse();
    }
}
