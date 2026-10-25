<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    protected $fillable = [
        'name', 
        'city', 
        'is_active'
    ];
    protected function casts(): array { 
        return ['is_active' => 'boolean']; 
    }

    public function stocks(): HasMany { return $this->hasMany(Stock::class); }
    public function stockMovements(): HasMany { return $this->hasMany(StockMovement::class); }
}
