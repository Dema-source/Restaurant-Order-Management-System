<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait for standardized API response formatting.
 *
 * This trait provides a set of helper methods to generate consistent JSON responses
 * across all API endpoints. It ensures that all API responses follow a uniform structure,
 * making it easier for frontend applications to consume the API and handle different
 * response types consistently.
 *
 * Response Structure:
 * - Success responses: { success: true, message: string, data: mixed }
 * - Error responses: { success: false, message: string, data: [], errors: mixed (optional) }
 * - Paginated responses: { success: true, message: string, data: array, pagination: object }
 *
 * All methods use Laravel's translation system for default messages, allowing for
 * easy localization of API responses.
 *
 */
trait ApiResponseTrait
{
    /**
     * Return a successful JSON response.
     *
     * This is the base method for all success responses. It returns a standardized
     * JSON structure with a success flag, message, and data payload.
     *
     * @param mixed $data The data to be included in the response (default: empty array)
     * @param string|null $message The success message (default: translated 'api.success')
     * @param int $code The HTTP status code (default: 200 OK)
     * @return JsonResponse The formatted JSON response
     */
    protected function successResponse(mixed $data = null, string $message = null, int $code = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message ?? __('api.success'),
            'data'    => $data ?? [],
        ], $code);
    }

    /**
     * Return a JSON response for successful resource creation.
     *
     * This method is specifically used after creating a new resource. It returns
     * a 201 Created status code, which is the standard HTTP response for successful
     * POST requests that create a new resource.
     *
     * @param mixed $data The created resource data (default: null)
     * @param string|null $message The success message (default: translated 'api.created')
     * @return JsonResponse The formatted JSON response with 201 status
     */
    protected function createdResponse(mixed $data = null, string $message = null): JsonResponse
    {
        return $this->successResponse($data, $message ?? __('api.created'), Response::HTTP_CREATED);
    }

    /**
     * Return a JSON response for successful deletion operations.
     *
     * This method is used after successfully deleting a resource. It returns
     * a 200 OK status code (rather than 204 No Content) to maintain consistency
     * with the API response structure while providing a success message.
     *
     * @param string|null $message The success message (default: translated 'api.deleted')
     * @return JsonResponse The formatted JSON response with 200 status
     */
    protected function noContentResponse(string $message = null): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message ?? __('api.deleted'),
            'data'    => [],
        ], Response::HTTP_OK);
    }

    /**
     * Return an error JSON response.
     *
     * This is the base method for all error responses. It returns a standardized
     * JSON structure with a success flag set to false, an error message, and
     * optionally detailed error information.
     *
     * @param string|null $message The error message (default: translated 'api.error')
     * @param int $code The HTTP status code (default: 500 Internal Server Error)
     * @param mixed $errors Detailed error information (default: null)
     * @return JsonResponse The formatted JSON error response
     */
    protected function errorResponse(string $message = null, int $code = Response::HTTP_INTERNAL_SERVER_ERROR, mixed $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message ?? __('api.error'),
            'data'    => [],
        ];

        // Include detailed error information if provided
        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Return a 404 Not Found JSON response.
     *
     * This method is used when a requested resource cannot be found. It returns
     * a 404 status code, which is the standard HTTP response for missing resources.
     *
     * @param string|null $message The error message (default: translated 'api.not_found')
     * @return JsonResponse The formatted JSON response with 404 status
     */
    protected function notFoundResponse(string $message = null): JsonResponse
    {
        return $this->errorResponse($message ?? __('api.not_found'), Response::HTTP_NOT_FOUND);
    }

    /**
     * Return a 422 Unprocessable Entity JSON response for validation errors.
     *
     * This method is specifically used for validation failures. It returns a 422
     * status code with detailed validation error information, which is the standard
     * HTTP response for requests that fail validation.
     *
     * @param mixed $errors The validation error details (typically from $validator->errors())
     * @param string|null $message The error message (default: translated 'api.validation_failed')
     * @return JsonResponse The formatted JSON response with 422 status
     */
    protected function validationErrorResponse(mixed $errors, string $message = null): JsonResponse
    {
        return $this->errorResponse($message ?? __('api.validation_failed'), Response::HTTP_UNPROCESSABLE_ENTITY, $errors);
    }

    /**
     * Return a 401 Unauthorized JSON response.
     *
     * This method is used when authentication is required but has failed or not
     * been provided. It returns a 401 status code, which is the standard HTTP response
     * for authentication failures.
     *
     * @param string|null $message The error message (default: translated 'api.unauthorized')
     * @return JsonResponse The formatted JSON response with 401 status
     */
    protected function unauthorizedResponse(string $message = null): JsonResponse
    {
        return $this->errorResponse($message ?? __('api.unauthorized'), Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Return a 403 Forbidden JSON response.
     *
     * This method is used when the user is authenticated but does not have permission
     * to access the requested resource. It returns a 403 status code, which is the
     * standard HTTP response for authorization failures.
     *
     * @param string|null $message The error message (default: translated 'api.forbidden')
     * @return JsonResponse The formatted JSON response with 403 status
     */
    protected function forbiddenResponse(string $message = null): JsonResponse
    {
        return $this->errorResponse($message ?? __('api.forbidden'), Response::HTTP_FORBIDDEN);
    }

    /**
     * Return a paginated JSON response.
     *
     * This method is used for returning paginated data sets. It includes the
     * paginated data items along with pagination metadata (total, per page,
     * current page, etc.) to help frontend applications implement pagination controls.
     *
     * @param mixed $data The paginated data (typically a LengthAwarePaginator instance)
     * @param string|null $message The success message (default: translated 'api.success')
     * @return JsonResponse The formatted JSON response with pagination metadata
     */
    protected function paginatedResponse(mixed $data, string $message = null): JsonResponse
    {
        return response()->json([
            'success'    => true,
            'message'    => $message ?? __('api.success'),
            'data'       => $data->items(),
            'pagination' => [
                'total'        => $data->total(),
                'per_page'     => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page'    => $data->lastPage(),
                'from'         => $data->firstItem(),
                'to'           => $data->lastItem(),
            ],
        ], Response::HTTP_OK);
    }
}
