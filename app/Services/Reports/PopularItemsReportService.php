<?php

namespace App\Services\Reports;

use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Builder;

/**
 * Popular Items Report Service
 *
 * Generates reports on the most popular menu items including:
 * - Items ranked by quantity sold
 * - Revenue generated per item
 * - Order frequency per item
 * - Top N items (configurable limit)
 *
 * Supports date range filtering to analyze popularity trends over time.
 *
 * @package App\Services\Reports
 */
class PopularItemsReportService extends BaseReportService
{
    /**
     * Generate popular items report.
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

        $query = OrderItem::query()
            ->with(['menuItem', 'order'])
            ->whereHas('order', function (Builder $q) use ($from, $to) {
                $q->where('status', '!=', 'cancelled');
                $this->applyDateFilter($q, 'created_at', $from, $to);
            });

        $orderItems = $query->get();

        // Group by menu item
        $itemsByQuantity = $orderItems
            ->groupBy('menu_item_id')
            ->map(function ($group) {
                $firstItem = $group->first();
                return [
                    'menu_item_id' => $firstItem->menu_item_id,
                    'menu_item_name' => $firstItem->menuItem->name ?? 'Unknown',
                    'menu_item_price' => (float) $firstItem->price,
                    'quantity_sold' => $group->sum('quantity'),
                    'total_revenue' => (float) $group->sum(function ($item) {
                        return $item->quantity * $item->price;
                    }),
                    'order_count' => $group->count(),
                ];
            })
            ->sortByDesc('quantity_sold')
            ->values();

        // Get top items
        $limit = request()->input('limit', 10);
        $topItems = $itemsByQuantity->take($limit);

        // Calculate summary
        $totalQuantity = $orderItems->sum('quantity');
        $totalRevenue = $orderItems->sum(function ($item) {
            return $item->quantity * $item->price;
        });
        $uniqueItems = $itemsByQuantity->count();

        return [
            'summary' => [
                'total_quantity_sold' => $totalQuantity,
                'total_revenue' => $totalRevenue,
                'unique_items_sold' => $uniqueItems,
                'date_range' => [
                    'from' => $from,
                    'to' => $to,
                ]
            ],
            'top_items' => $topItems,
            'all_items' => $itemsByQuantity,
        ];
    }
}
