<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'discount_type',
        'discount_value',
        'minimum_order_amount',
        'weekday',
        'is_active',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'minimum_order_amount' => 'decimal:2',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Boot the model and add global scopes.
     *
     * This method adds a global scope to automatically filter inactive
     * discounts for non-admin users. This ensures that regular users
     * only see active discounts while administrators
     * can see all discounts including inactive ones.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope('active_for_non_admin', function ($query) {
            if (auth()->check() && !auth()->user()->hasRole('super_administrator')) {
                $query->where('is_active', true);
            }
        });
    }

    // Relations
    /**
     * The orders that belong to the discount
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // Scopes
    /**
     * Scope to filter only active discounts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter current discounts.
     *
     * Current means:
     * - active
     * - inside date range
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }

    /**
     * Scope to search discounts by name.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search The search term
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where('name', 'like', "%{$search}%");
    }

    /**
     * Scope to filter discounts by creation date range.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $from Start date (Y-m-d format)
     * @param string|null $to End date (Y-m-d format)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDateRange($query, ?string $from = null, ?string $to = null)
    {
        if ($from) {
            $query->where('created_at', '>=', $from);
        }

        if ($to) {
            $query->where('created_at', '<=', $to);
        }

        return $query;
    }

    /**
     * Scope to filter discounts by type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type The discount type (percentage or fixed)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('discount_type', $type);
    }

    /**
     * Scope to filter discounts by weekday.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $weekday The weekday (Monday, Tuesday, etc.)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByWeekday($query, string $weekday)
    {
        return $query->where('weekday', $weekday);
    }

    /**
     * Scope to filter discounts by minimum order amount.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param float $subtotal The order subtotal
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByMinimumOrderAmount($query, float $subtotal)
    {
        return $query->where(function ($q) use ($subtotal) {
            $q->whereNull('minimum_order_amount')
                ->orWhere('minimum_order_amount', '<=', $subtotal);
        });
    }

    /**
     * Check if the discount is currently valid.
     *
     * A discount is valid if:
     * - is_active is true
     * - start_date is null or in the past
     * - end_date is null or in the future
     * - weekday is null or matches today
     *
     * @return bool True if valid, false otherwise
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->start_date && $this->start_date->isFuture()) {
            return false;
        }

        if ($this->end_date && $this->end_date->isPast()) {
            return false;
        }

        if ($this->weekday && $this->weekday !== now()->format('l')) {
            return false;
        }

        return true;
    }

    /**
     * Check if the discount is eligible for a given subtotal.
     *
     * @param float $subtotal The order subtotal
     * @return bool True if eligible, false otherwise
     */
    public function isEligible(float $subtotal): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        if ($this->minimum_order_amount && $subtotal < $this->minimum_order_amount) {
            return false;
        }

        return true;
    }

    /**
     * Calculate the discount amount for a given subtotal.
     *
     * @param float $subtotal The order subtotal
     * @return float The discount amount
     */
    public function calculateDiscountAmount(float $subtotal): float
    {
        if ($this->discount_type === 'percentage') {
            return ($subtotal * $this->discount_value) / 100;
        }

        return $this->discount_value;
    }
}
