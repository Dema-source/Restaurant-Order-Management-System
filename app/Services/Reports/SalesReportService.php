<?php

namespace App\Services\Reports;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;

/**
 * Sales Report Service
 * 
 * Generates comprehensive sales reports including:
 * - Total sales, revenue, and discounts
 * - Sales by period (daily, weekly, monthly, yearly)
 * - Sales by order status
 * - Average order value metrics
 * 
 * Supports date range filtering and period-based grouping.
 * 
 * @package App\Services\Reports
 */
class SalesReportService extends BaseReportService
{
    /**
     * Generate sales report.
     *
     * @return array
     */
    public function generate(): array
    {
        $from = $this->dateFrom();
        $to = $this->dateTo();

        // Use default date range if not provided
        if (!$from && !$to) {
            $defaultRange = $this->getDefaultDateRange();
            $from = $defaultRange['from'];
            $to = $defaultRange['to'];
        }

        // Validate date range
        $validation = $this->validateDateRange($from, $to);
        if (!$validation['valid']) {
            return [
                'error' => $validation['error'],
                'data' => []
            ];
        }

        $query = Order::query()
            ->with(['items', 'customer'])
            ->where('status', '!=', 'cancelled');

        $this->applyDateFilter($query, 'created_at', $from, $to);

        $orders = $query->get();

        // Calculate sales metrics
        $totalSales = $orders->sum('total_amount');
        $totalDiscount = $orders->sum('discount_amount');
        $totalRevenue = $orders->sum('total_amount') - $orders->sum('discount_amount');
        $totalOrders = $orders->count();
        $averageOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        // Group by period
        $period = $this->getPeriodType();
        $salesByPeriod = $this->getSalesByPeriod($from, $to, $period);

        // Sales by status
        $salesByStatus = $orders->groupBy('status')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total' => $group->sum('total_amount'),
                'average' => $group->avg('total_amount')
            ];
        });

        return [
            'summary' => [
                'total_sales' => $totalSales,
                'total_discount' => $totalDiscount,
                'total_revenue' => $totalRevenue,
                'total_orders' => $totalOrders,
                'average_order_value' => $averageOrderValue,
                'date_range' => [
                    'from' => $from,
                    'to' => $to,
                ]
            ],
            'sales_by_period' => $salesByPeriod,
            'sales_by_status' => $salesByStatus,
        ];
    }

    /**
     * Get sales grouped by period.
     *
     * @param string|null $from
     * @param string|null $to
     * @param string $period
     * @return array
     */
    protected function getSalesByPeriod(?string $from, ?string $to, string $period = 'daily'): array
    {
        $query = Order::query()
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total, COUNT(*) as orders')
            ->where('status', '!=', 'cancelled');

        $this->applyDateFilter($query, 'created_at', $from, $to);

        $query->groupBy('date')
            ->orderBy('date');

        $results = $query->get();

        return $results->map(function ($item) {
            return [
                'date' => $item->date,
                'total' => (float) $item->total,
                'orders' => $item->orders,
            ];
        })->toArray();
    }
}
