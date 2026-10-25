<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'product_id', 
        'warehouse_id', 
        'movement_type', 
        'quantity', 
        'reference_id', 
        'reference_type', 
        'note', 
        'moved_at'
    ];
    protected function casts(): array { return ['quantity' => 'integer', 'moved_at' => 'datetime']; }

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
}
