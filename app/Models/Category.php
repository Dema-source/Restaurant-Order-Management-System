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
        'description'
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
     * Get all of the menu_items for the Category
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function menu_items(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }
}
