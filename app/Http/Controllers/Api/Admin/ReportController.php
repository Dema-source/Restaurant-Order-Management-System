<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\Reports\SalesReportService;
use App\Services\Reports\PopularItemsReportService;
use Illuminate\Http\JsonResponse;

class ReportController extends BaseApiController
{
    public function __construct(
        protected SalesReportService $salesReportService,
        protected PopularItemsReportService $popularItemsReportService,
    ) {}

    public function sales(): JsonResponse
    {
        $data = $this->salesReportService->generate();

        if (isset($data['error'])) {
            return $this->errorResponse($data['error'], 400);
        }

        return $this->successResponse($data);
    }

    public function popularItems(): JsonResponse
    {
        $data = $this->popularItemsReportService->generate();

        if (isset($data['error'])) {
            return $this->errorResponse($data['error'], 400);
        }

        return $this->successResponse($data);
    }
}
