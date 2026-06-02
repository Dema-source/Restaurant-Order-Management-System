<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    public array $translatable = [
        'name',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'is_active'         => 'boolean',
        ];
    }

    /**
     * Boot the model and add global scopes.
     *
     * This method adds a global scope to automatically filter inactive
     * categories for non-admin users. This ensures that regular users
     * only see active categories while administrators
     * can see all categories including inactive ones.
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

    /**
     * Get all of the menu_items for the Category
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function menu_items(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    // Scopes
    /**
     * Scope to filter only active categories.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to search categories by name in all available locales.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search The search term
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where('name->en', 'like', "%{$search}%")
                    ->orWhere('name->ar', 'like', "%{$search}%");
    }

    /**
     * Scope to filter categories by creation date range.
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
}
