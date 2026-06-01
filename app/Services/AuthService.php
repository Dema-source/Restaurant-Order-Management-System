<?php

namespace App\Services;

use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService extends BaseService
{
    public function __construct(
        UserRepositoryInterface $userRepository
    ) {
        parent::__construct($userRepository);
    }

    public function login(LoginRequest $request): array
    {
        $user = $this->repository->findByField('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        $token = $user->createToken('api-token', expiresAt: now()->addMinutes(15))->plainTextToken;

        return [
            'user' => $user,
            'accessToken' => $token,
            'refreshToken' => $token,
            'expiresIn' => 900,
        ];
    }

    public function logout(mixed $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
