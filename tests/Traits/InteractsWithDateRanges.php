<?php

namespace Tests\Traits;

trait InteractsWithDateRanges
{
    protected function getTodayRange(): array
    {
        return [
            now()->startOfDay()->format('Y-m-d'),
            now()->endOfDay()->format('Y-m-d'),
        ];
    }

    protected function getYesterdayRange(): array
    {
        return [
            now()->subDay()->startOfDay()->format('Y-m-d'),
            now()->subDay()->endOfDay()->format('Y-m-d'),
        ];
    }

    protected function getLastWeekRange(): array
    {
        return [
            now()->subWeek()->startOfWeek()->format('Y-m-d'),
            now()->subWeek()->endOfWeek()->format('Y-m-d'),
        ];
    }

    protected function getLastMonthRange(): array
    {
        return [
            now()->subMonth()->startOfMonth()->format('Y-m-d'),
            now()->subMonth()->endOfMonth()->format('Y-m-d'),
        ];
    }

    protected function getCustomRange(int $daysBack, int $daysForward = 0): array
    {
        return [
            now()->subDays($daysBack)->format('Y-m-d'),
            now()->addDays($daysForward)->format('Y-m-d'),
        ];
    }

    protected function createDateRangeQuery(int $daysBack = 3, int $daysForward = 0): string
    {
        [$from, $to] = $this->getCustomRange($daysBack, $daysForward);
        return "from={$from}&to={$to}";
    }
}
