<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends BaseApiController
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $payload = $this->authService->login($request);

        return $this->successResponse([
            'user' => new UserResource($payload['user']),
            'accessToken' => $payload['accessToken'],
            'refreshToken' => $payload['refreshToken'],
            'expiresIn' => $payload['expiresIn'],
        ], 'Login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->successResponse(null, 'Logged out successfully');
    }

}
