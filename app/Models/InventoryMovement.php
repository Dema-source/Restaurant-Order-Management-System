<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'menu_item_id',
        'order_id',
        'type',
        'quantity',
        'reason',
        'notes',
        'created_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Boot the model and add automatic created_by assignment.
     *
     * This method automatically assigns the authenticated user's ID
     * to the created_by field when creating a new inventory movement.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }

            // If created_at is not provided, use current timestamp
            if (empty($model->created_at)) {
                $model->created_at = now();
            }
        });

        static::created(function ($model) {
            // Update stock_quantity in menu_items when inventory movement is created
            $menuItem = $model->menuItem;
            if ($menuItem) {
                if ($model->type === 'in') {
                    $menuItem->increment('stock_quantity', $model->quantity);
                } elseif ($model->type === 'out') {
                    $menuItem->decrement('stock_quantity', $model->quantity);
                }
            }
        });
    }

    // Scopes
    /**
     * Scope to filter inventory movements by type (in/out).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type The movement type (in or out)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter inventory movements by reason.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $reason The movement reason (order, restock, waste, adjustment)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByReason($query, string $reason)
    {
        return $query->where('reason', $reason);
    }

    /**
     * Scope to filter inventory movements by menu item.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $menuItemId The menu item ID
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByMenuItem($query, int $menuItemId)
    {
        return $query->where('menu_item_id', $menuItemId);
    }

    /**
     * Scope to filter inventory movements by order.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $orderId The order ID
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByOrder($query, int $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    /**
     * Scope to filter inventory movements by creation date range.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $from Start date (Y-m-d format)
     * @param string|null $to End date (Y-m-d format)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDateRange($query, ?string $from = null, ?string $to = null)
    {
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query;
    }

    /**
     * Scope to search inventory movements by reason or notes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search The search term
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where('reason', 'like', "%{$search}%")
            ->orWhere('notes', 'like', "%{$search}%");
    }

    // Relationships
    /**
     * Get the menu item that owns the inventory movement.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    /**
     * Get the order that owns the inventory movement.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user who created the inventory movement.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
