<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface DiscountRepositoryInterface extends RepositoryInterface
{
    public function getPaginatedWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;

    public function toggleActive(int $id): bool;

    /**
     * Get all active discounts.
     *
     * @return Collection Collection of active discounts
     */
    public function getActiveDiscounts(): Collection;

    /**
     * Get all current discounts (active and within date range).
     *
     * @return Collection Collection of current discounts
     */
    public function getCurrentDiscounts(): Collection;

    /**
     * Get discounts by weekday.
     *
     * @param string $weekday The weekday (Monday, Tuesday, etc.)
     * @return Collection Collection of discounts for the weekday
     */
    public function getWeekdayDiscounts(string $weekday): Collection;

    /**
     * Get discounts with minimum order amount condition.
     *
     * @param float $subtotal The order subtotal
     * @return Collection Collection of eligible discounts
     */
    public function getSubtotalDiscounts(float $subtotal): Collection;

    /**
     * Get eligible discounts for a given subtotal.
     *
     * This method filters discounts that are:
     * - active
     * - within date range
     * - match current weekday (if set)
     * - meet minimum order amount requirement
     *
     * @param float $subtotal The order subtotal
     * @return Collection Collection of eligible discounts
     */
    public function getEligibleDiscounts(float $subtotal): Collection;

    /**
     * Find the best discount for a given subtotal.
     *
     * @param float $subtotal The order subtotal
     * @return Model|null The best discount or null if none applicable
     */
    public function findBestDiscount(float $subtotal): ?Model;
}
