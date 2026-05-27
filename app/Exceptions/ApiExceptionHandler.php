<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiExceptionHandler
{
    public static function render(Request $request, \Throwable $e): ?JsonResponse
    {
        if (!$request->expectsJson() && !$request->is('api/*')) {
            return null;
        }

        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found',
            ], Response::HTTP_NOT_FOUND);
        }

        if ($e instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json([
            'success' => false,
            'message' => config('app.debug') ? $e->getMessage() : 'Server Error',
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
