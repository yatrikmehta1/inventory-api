<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockAdjustmentService
{
    public function adjust(array $data): array
    {
        $result = DB::transaction(function () use ($data) {
            // Create the balance if absent. The unique composite index protects concurrent inserts.
            // INSERT IGNORE + unique index makes initialization safe when two requests race.
            DB::table('stocks')->insertOrIgnore([
                'product_id' => $data['product_id'], 'warehouse_id' => $data['warehouse_id'],
                'quantity' => 0, 'reserved_quantity' => 0,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $stock = Stock::query()->where('product_id', $data['product_id'])
                ->where('warehouse_id', $data['warehouse_id'])->lockForUpdate()->firstOrFail();
            $n = (int) $data['quantity'];
            $available = $stock->quantity - $stock->reserved_quantity;
            switch ($data['movement_type']) {
                case 'stock_in': $stock->quantity += $n; break;
                case 'stock_out':
                    if ($available < $n || $stock->quantity < $n) throw ValidationException::withMessages(['quantity' => 'Insufficient available stock.']);
                    $stock->quantity -= $n; break;
                case 'reservation':
                    if ($available < $n) throw ValidationException::withMessages(['quantity' => 'Insufficient available stock to reserve.']);
                    $stock->reserved_quantity += $n; break;
                case 'reservation_release':
                    if ($stock->reserved_quantity < $n) throw ValidationException::withMessages(['quantity' => 'Cannot release more stock than is reserved.']);
                    $stock->reserved_quantity -= $n; break;
                default: throw ValidationException::withMessages(['movement_type' => 'Unsupported movement type.']);
            }
            $stock->save();
            $movement = StockMovement::create([
                'product_id' => $stock->product_id, 'warehouse_id' => $stock->warehouse_id,
                'movement_type' => $data['movement_type'], 'quantity' => $n,
                'reference_id' => $data['reference_id'] ?? null, 'reference_type' => $data['reference_type'] ?? null,
                'note' => $data['note'] ?? null, 'moved_at' => now(),
            ]);
            return ['stock' => $stock->fresh(), 'movement' => $movement];
        }, attempts: 3);
        // Only invalidate after the transaction commits successfully.
        app(CacheInvalidationService::class)->stockChanged();
        return $result;
    }
}