<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponseTrait
{
    protected function successResponse(mixed $data = null, string $message = null, int $code = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message ?? __('api.success'),
            'data'    => $data ?? [],
        ], $code);
    }

    protected function createdResponse(mixed $data = null, string $message = null): JsonResponse
    {
        return $this->successResponse($data, $message ?? __('api.created'), Response::HTTP_CREATED);
    }

    protected function noContentResponse(string $message = null): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message ?? __('api.deleted'),
            'data'    => [],
        ], Response::HTTP_OK);
    }

    protected function errorResponse(string $message = null, int $code = Response::HTTP_INTERNAL_SERVER_ERROR, mixed $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message ?? __('api.error'),
            'data'    => [],
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    protected function notFoundResponse(string $message = null): JsonResponse
    {
        return $this->errorResponse($message ?? __('api.not_found'), Response::HTTP_NOT_FOUND);
    }

    protected function validationErrorResponse(mixed $errors, string $message = null): JsonResponse
    {
        return $this->errorResponse($message ?? __('api.validation_failed'), Response::HTTP_UNPROCESSABLE_ENTITY, $errors);
    }

    protected function unauthorizedResponse(string $message = null): JsonResponse
    {
        return $this->errorResponse($message ?? __('api.unauthorized'), Response::HTTP_UNAUTHORIZED);
    }

    protected function forbiddenResponse(string $message = null): JsonResponse
    {
        return $this->errorResponse($message ?? __('api.forbidden'), Response::HTTP_FORBIDDEN);
    }

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
