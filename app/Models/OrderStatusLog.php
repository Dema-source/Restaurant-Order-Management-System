<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order_status_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'old_status',
        'new_status',
        'changed_by',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Boot the model and add automatic field assignments.
     *
     * This method automatically assigns:
     * - changed_by: The authenticated user's ID when creating a status log
     * - created_at: Current timestamp if not provided
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->changed_by = auth()->id();
            }

            // If created_at is not provided, use current timestamp
            if (empty($model->created_at)) {
                $model->created_at = now();
            }
        });
    }

    /**
     * Get the order that owns the status log.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user who changed the status.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    // Scopes
    /**
     * Scope to search status logs by order number or notes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search The search term
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('notes', 'like', "%{$search}%")
                ->orWhereHas('order', function ($orderQuery) use ($search) {
                    $orderQuery->where('order_number', 'like', "%{$search}%");
                });
        });
    }

    /**
     * Scope to filter status logs by date range.
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
     * Scope to filter status logs by order ID.
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
     * Scope to filter status logs by status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status The status (old or new)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where(function ($q) use ($status) {
            $q->where('old_status', $status)
                ->orWhere('new_status', $status);
        });
    }
}
