<?php

namespace App\Services\Reports;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Base Report Service
 * 
 * Abstract base class for all report services. Provides common functionality
 * for date filtering, validation, formatting, and pagination across all report types.
 * 
 * This service does not extend BaseService as reports are read-only analytical
 * operations, not CRUD operations.
 * 
 * @package App\Services\Reports
 */
abstract class BaseReportService
{
    /**
     * Get date from parameter from request.
     *
     * @param string|null $default
     * @return string|null
     */
    protected function dateFrom(?string $default = null): ?string
    {
        return request()->input('date_from') ?? $default;
    }

    /**
     * Get date to parameter from request.
     *
     * @param string|null $default
     * @return string|null
     */
    protected function dateTo(?string $default = null): ?string
    {
        return request()->input('date_to') ?? $default;
    }

    /**
     * Validate date range.
     *
     * @param string|null $from
     * @param string|null $to
     * @return array{valid: bool, error: string|null}
     */
    protected function validateDateRange(?string $from, ?string $to): array
    {
        if ($from && $to) {
            try {
                $fromDate = Carbon::parse($from);
                $toDate = Carbon::parse($to);

                if ($fromDate->gt($toDate)) {
                    return [
                        'valid' => false,
                        'error' => 'Date from must be before or equal to date to'
                    ];
                }
            } catch (\Exception $e) {
                return [
                    'valid' => false,
                    'error' => 'Invalid date format'
                ];
            }
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Get default date range (last 30 days).
     *
     * @return array{from: string, to: string}
     */
    protected function getDefaultDateRange(): array
    {
        return [
            'from' => Carbon::now()->subDays(30)->startOfDay()->toDateString(),
            'to' => Carbon::now()->endOfDay()->toDateString(),
        ];
    }

    /**
     * Apply date filter to query.
     *
     * @param Builder $query
     * @param string $column
     * @param string|null $from
     * @param string|null $to
     * @return Builder
     */
    protected function applyDateFilter(Builder $query, string $column = 'created_at', ?string $from = null, ?string $to = null): Builder
    {
        $from = $from ?? $this->dateFrom();
        $to = $to ?? $this->dateTo();

        if ($from) {
            $query->whereDate($column, '>=', $from);
        }

        if ($to) {
            $query->whereDate($column, '<=', $to);
        }

        return $query;
    }

    /**
     * Format currency amount.
     *
     * @param float $amount
     * @param string $currency
     * @return string
     */
    protected function formatCurrency(float $amount, string $currency = '$'): string
    {
        return $currency . number_format($amount, 2);
    }

    /**
     * Paginate query results.
     *
     * @param Builder $query
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    protected function paginateResults(Builder $query, int $perPage = 15): LengthAwarePaginator
    {
        return $query->paginate($perPage);
    }

    /**
     * Get period type from request.
     *
     * @return string
     */
    protected function getPeriodType(): string
    {
        return request()->input('period', 'daily');
    }

    /**
     * Group query by period.
     *
     * @param Builder $query
     * @param string $column
     * @param string $period
     * @return Builder
     */
    protected function groupByPeriod(Builder $query, string $column = 'created_at', string $period = 'daily'): Builder
    {
        return match ($period) {
            'hourly' => $query->selectRaw('DATE_FORMAT(' . $column . ', "%Y-%m-%d %H:00:00") as period'),
            'daily' => $query->selectRaw('DATE(' . $column . ') as period'),
            'weekly' => $query->selectRaw('DATE_FORMAT(' . $column . ', "%Y-%u") as period'),
            'monthly' => $query->selectRaw('DATE_FORMAT(' . $column . ', "%Y-%m") as period'),
            'yearly' => $query->selectRaw('YEAR(' . $column . ') as period'),
            default => $query->selectRaw('DATE(' . $column . ') as period'),
        };
    }

    /**
     * Generate cache key for report.
     *
     * @param string $reportName
     * @param array $params
     * @return string
     */
    protected function cacheKey(string $reportName, array $params = []): string
    {
        return 'report:' . $reportName . ':' . md5(json_encode($params));
    }

    /**
     * Remember cached result.
     *
     * @param string $key
     * @param callable $callback
     * @param int $ttl
     * @return mixed
     */
    protected function remember(string $key, callable $callback, int $ttl = 3600): mixed
    {
        return cache()->remember($key, $ttl, $callback);
    }
}
