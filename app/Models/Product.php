<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id', 
        'name', 
        'sku', 
        'description', 
        'base_price', 
        'is_active'
    ];
    protected function casts(): array { 
        return ['base_price' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function stocks(): HasMany { return $this->hasMany(Stock::class); }
    public function stockMovements(): HasMany { return $this->hasMany(StockMovement::class); }
    public function scopeActive(Builder $query): Builder { return $query->where('is_active', true); }
    public function scopePriceRange(Builder $query, ?float $min, ?float $max): Builder
    {
        return $query->when($min !== null, fn ($q) => $q->where('base_price', '>=', $min))
            ->when($max !== null, fn ($q) => $q->where('base_price', '<=', $max));
    }
}
