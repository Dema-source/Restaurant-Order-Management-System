<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class MenuItem extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'image',
    ];

    public array $translatable = [
        'name',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'price'        => 'decimal:2',
            'is_available' => 'boolean',
        ];
    }

    /**
     * Get the category that owns the menu item
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
