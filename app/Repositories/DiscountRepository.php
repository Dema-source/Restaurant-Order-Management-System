<?php

namespace App\Repositories;

use App\Models\Discount;
use App\Repositories\Contracts\DiscountRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class DiscountRepository extends BaseRepository implements DiscountRepositoryInterface
{
    public function __construct(Discount $model)
    {
        parent::__construct($model);
    }

    /**
     * Get paginated discounts with filters.
     *
     * This method applies search, active status, and date range filters
     * to the discount query and returns paginated results.
     *
     * @param array $filters The filter criteria
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator Paginated results
     */
    public function getPaginatedWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Apply search filter if provided
        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Apply active status filter if provided
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', $filters['is_active']);
        }

        // Apply discount type filter if provided
        if (isset($filters['discount_type']) && !empty($filters['discount_type'])) {
            $query->byType($filters['discount_type']);
        }

        // Apply weekday filter if provided
        if (isset($filters['weekday']) && !empty($filters['weekday'])) {
            $query->byWeekday($filters['weekday']);
        }

        // Apply date range filter if provided
        $query->dateRange(
            from: $filters['created_at_from'] ?? null,
            to: $filters['created_at_to'] ?? null
        );

        return $query->paginate($perPage);
    }

    /**
     * Toggle the active status of a discount.
     *
     * @param int $id The discount ID
     * @return bool True if successful, false otherwise
     */
    public function toggleActive(int $id): bool
    {
        $discount = $this->model->findOrFail($id);
        return $discount->update(['is_active' => !$discount->is_active]);
    }

    /**
     * Find the best discount for a given subtotal.
     *
     * This method searches for the best applicable discount based on:
     * - Active status
     * - Minimum order amount requirement
     * - Valid date range
     * - Weekday condition
     * - Maximum discount value
     *
     * @param float $subtotal The order subtotal
     * @return Model|null The best discount or null if none applicable
     */
    public function findBestDiscount(float $subtotal): ?Model
    {
        $eligibleDiscounts = $this->getEligibleDiscounts($subtotal);

        $bestDiscount = null;
        $maxDiscountAmount = 0;

        foreach ($eligibleDiscounts as $discount) {
            $discountAmount = $discount->calculateDiscountAmount($subtotal);
            if ($discountAmount > $maxDiscountAmount) {
                $maxDiscountAmount = $discountAmount;
                $bestDiscount = $discount;
            }
        }

        return $bestDiscount;
    }

    /**
     * Get all active discounts.
     *
     * @return Collection Collection of active discounts
     */
    public function getActiveDiscounts(): Collection
    {
        return $this->model->active()->get();
    }

    /**
     * Get all current discounts (active and within date range).
     *
     * @return Collection Collection of current discounts
     */
    public function getCurrentDiscounts(): Collection
    {
        return $this->model->current()->get();
    }

    /**
     * Get discounts by weekday.
     *
     * @param string $weekday The weekday (Monday, Tuesday, etc.)
     * @return Collection Collection of discounts for the weekday
     */
    public function getWeekdayDiscounts(string $weekday): Collection
    {
        return $this->model->active()->byWeekday($weekday)->get();
    }

    /**
     * Get discounts with minimum order amount condition.
     *
     * @param float $subtotal The order subtotal
     * @return Collection Collection of eligible discounts
     */
    public function getSubtotalDiscounts(float $subtotal): Collection
    {
        return $this->model->active()->byMinimumOrderAmount($subtotal)->get();
    }

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
    public function getEligibleDiscounts(float $subtotal): Collection
    {
        $currentWeekday = now()->format('l');

        return $this->model->current()
            ->where(function ($query) use ($currentWeekday) {
                $query->whereNull('weekday')
                    ->orWhere('weekday', $currentWeekday);
            })
            ->byMinimumOrderAmount($subtotal)
            ->get();
    }
}
