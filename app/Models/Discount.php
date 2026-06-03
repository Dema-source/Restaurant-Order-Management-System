<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'type',
        'value',
        'start_date',
        'end_date',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'value'      => 'decimal:2',
            'start_date' => 'date',
            'end_date'   => 'date',
            'is_active'  => 'boolean',
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
     * Scope to search discounts by name or code.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search The search term
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where('name', 'like', "%{$search}%")
            ->orWhere('code', 'like', "%{$search}%");
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
     * Scope to filter discounts by validity period.
     *
     * This scope filters discounts that are currently valid based on
     * their start_date and end_date. A discount is valid if:
     * - start_date is null or in the past
     * - end_date is null or in the future
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeValid($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('start_date')
              ->orWhere('start_date', '<=', now());
        })->where(function ($q) {
            $q->whereNull('end_date')
              ->orWhere('end_date', '>=', now());
        });
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
        return $query->where('type', $type);
    }

    /**
     * Check if the discount is currently valid.
     *
     * A discount is valid if:
     * - is_active is true
     * - start_date is null or in the past
     * - end_date is null or in the future
     *
     * This method is useful for business logic when applying discounts
     * to orders or checking if a discount can be used.
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

        return true;
    }

    /**
     * Check if the discount has expired.
     *
     * A discount is considered expired if the end_date has passed.
     * This method is useful for filtering out expired discounts
     * in business logic or displaying expiration status.
     *
     * @return bool True if expired, false otherwise
     */
    public function isExpired(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    /**
     * Check if the discount has not started yet.
     *
     * A discount is considered not started if the start_date is in the future.
     * This method is useful for displaying upcoming discounts or
     * preventing use of discounts before their validity period.
     *
     * @return bool True if not started, false otherwise
     */
    public function isNotStarted(): bool
    {
        return $this->start_date && $this->start_date->isFuture();
    }
}
