<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Centralized API exception handler for consistent JSON error responses.
 *
 * This class provides a standardized way to handle various exceptions and convert them
 * into consistent JSON responses for API endpoints. It ensures that all API errors follow
 * a uniform structure, making it easier for frontend applications to handle errors.
 *
 * The handler only processes requests that expect JSON responses or are API routes,
 * allowing the default Laravel exception handler to handle web requests.
 *
 * Supported exception types:
 * - ValidationException: Returns validation errors with 422 status
 * - ModelNotFoundException/NotFoundHttpException: Returns 404 for missing resources
 * - AuthenticationException: Returns 401 for unauthenticated requests
 * - Generic exceptions: Returns 500 with error message (debug mode only)
 *
 */
class ApiExceptionHandler
{
    /**
     * Render an exception into a JSON response.
     *
     * This method acts as a central exception handler for API requests. It checks
     * the type of exception and returns an appropriate JSON response with the
     * correct HTTP status code. Only processes requests that expect JSON or are
     * API routes, returning null for other requests to let Laravel handle them.
     *
     * @param Request $request The incoming HTTP request
     * @param \Throwable $e The exception that was thrown
     * @return JsonResponse|null JSON response for API requests, null for web requests
     */
    public static function render(Request $request, \Throwable $e): ?JsonResponse
    {
        // Only handle API requests or requests expecting JSON
        // Return null to let Laravel's default handler process web requests
        if (!$request->expectsJson() && !$request->is('api/*')) {
            return null;
        }

        // Handle validation exceptions (form request validation failures)
        // Returns 422 Unprocessable Entity with detailed validation errors
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Handle model not found and HTTP not found exceptions
        // Returns 404 Not Found when a resource cannot be located
        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found',
            ], Response::HTTP_NOT_FOUND);
        }

        // Handle authentication exceptions
        // Returns 401 Unauthorized when the user is not authenticated
        if ($e instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Handle all other exceptions as internal server errors
        // In debug mode, returns the actual error message for debugging
        // In production, returns a generic "Server Error" message for security
        return response()->json([
            'success' => false,
            'message' => config('app.debug') ? $e->getMessage() : 'Server Error',
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
