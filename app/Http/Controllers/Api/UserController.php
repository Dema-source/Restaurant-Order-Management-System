<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends BaseApiController
{
    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * Show authenticated user
     * 
     * Returns the current authenticated user's information.
     * 
     * @return JsonResponse JSON response with user data
     */
    public function me(): JsonResponse
    {
        $user = $this->userService->me(auth()->user());

        return $this->successResponse(
            new UserResource($user)
        );
    }

    /**
     * Change password
     * 
     * Allows users to change their own password or admins to change any user's password.
     * - Regular users: Must provide old_password for verification
     * - Admins: Can change any user's password without old_password
     * 
     * @param ChangePasswordRequest $request Validated request with password data
     * @param int|null $id User ID (optional, for admin to change other user's password)
     * @return JsonResponse JSON response with success message
     */
    public function changePassword(ChangePasswordRequest $request, ?int $id = null): JsonResponse
    {
        $userId = $id ?? auth()->id();

        // Check authorization: only admin can change other user's password
        if ($id && $id !== auth()->id() && !auth()->user()?->hasRole('super_administrator')) {
            abort(403, 'Unauthorized');
        }

        $this->userService->changePassword(
            $userId,
            $request->password,
            $request->old_password
        );

        return $this->successResponse(null, 'Password changed successfully');
    }
}
