<?php

namespace Tests\Traits;

use Illuminate\Testing\TestResponse;

trait InteractsWithResponses
{
    protected function assertSuccessResponse(TestResponse $response, ?array $structure = null): void
    {
        $response->assertOk()->assertJson([
            'success' => true,
        ]);

        if ($structure) {
            $response->assertJsonStructure($structure);
        }
    }

    protected function assertCreatedResponse(TestResponse $response, ?array $structure = null): void
    {
        $response->assertCreated()->assertJson([
            'success' => true,
        ]);

        if ($structure) {
            $response->assertJsonStructure($structure);
        }
    }

    protected function assertNoContentResponse(TestResponse $response): void
    {
        $response->assertNoContent();
    }

    protected function assertUnauthorizedResponse(TestResponse $response): void
    {
        $response->assertUnauthorized()->assertJson([
            'success' => false,
        ]);
    }

    protected function assertForbiddenResponse(TestResponse $response): void
    {
        $response->assertForbidden()->assertJson([
            'success' => false,
        ]);
    }

    protected function assertNotFoundResponse(TestResponse $response): void
    {
        $response->assertNotFound()->assertJson([
            'success' => false,
        ]);
    }

    protected function assertValidationErrorResponse(TestResponse $response, array $fields): void
    {
        $response->assertUnprocessable()->assertJson([
            'success' => false,
        ])->assertJsonValidationErrors($fields);
    }

    protected function assertApiErrorResponse(TestResponse $response, int $status = 500): void
    {
        $response->assertStatus($status)->assertJson([
            'success' => false,
        ]);
    }

    protected function assertPaginatedResponse(TestResponse $response, int $count, string $path = 'data'): void
    {
        $response->assertOk()->assertJsonStructure([
            'success',
            'message',
            'data',
        ])->assertJsonCount($count, $path);
    }

    protected function assertResourceResponse(TestResponse $response, array $expectedData): void
    {
        $response->assertOk()->assertJson([
            'success' => true,
        ]);

        foreach ($expectedData as $key => $value) {
            $response->assertJsonPath($key, $value);
        }
    }
}
