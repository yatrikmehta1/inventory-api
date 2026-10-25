<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class InventoryReportService
{
    public function summary(): array
    {
        return Cache::remember('inventory:summary:v1', now()->addMinutes(5), function () {
            // Aggregation happens in SQL; LEFT JOIN keeps products with no stock rows.
            $totals = DB::table('stocks')->select('product_id',
                DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(reserved_quantity) as total_reserved'))
                ->groupBy('product_id');
            $topWarehouseId = DB::table('stocks as s2')->select('s2.warehouse_id')
                ->whereColumn('s2.product_id', 'products.id')->orderByDesc('s2.quantity')
                ->orderBy('s2.warehouse_id')->limit(1);
            $topWarehouseName = DB::table('stocks as s2')
                ->join('warehouses as w', 'w.id', '=', 's2.warehouse_id')
                ->select('w.name')
                ->whereColumn('s2.product_id', 'products.id')
                ->orderByDesc('s2.quantity')
                ->orderBy('s2.warehouse_id')
                ->limit(1);
            $rows = Product::query()->leftJoinSub($totals, 'totals', 'totals.product_id', '=', 'products.id')
                ->select('products.id', 'products.name', 'products.sku',
                    DB::raw('COALESCE(totals.total_quantity, 0) as total_quantity'),
                    DB::raw('COALESCE(totals.total_reserved, 0) as total_reserved'),
                    DB::raw('COALESCE(totals.total_quantity, 0) - COALESCE(totals.total_reserved, 0) as available_quantity'))
                ->selectSub($topWarehouseId, 'top_warehouse_id')
                ->selectSub($topWarehouseName, 'top_warehouse_name')
                ->orderBy('products.id')->get();
            return $rows->map(fn ($r) => [
                'product_id' => (int)$r->id, 'name' => $r->name, 'sku' => $r->sku,
                'total_quantity' => (int)$r->total_quantity, 'total_reserved' => (int)$r->total_reserved,
                'available_quantity' => (int)$r->available_quantity,
                'highest_stock_warehouse' => $r->top_warehouse_id ? ['id' => (int)$r->top_warehouse_id, 'name' => $r->top_warehouse_name] : null,
                'zero_total_stock' => (int)$r->total_quantity === 0,
            ])->all();
        });
    }

    public function lowStock(int $threshold): array
    {
        $version = (int) Cache::get('inventory:low-stock:version', 1);
        $key = "inventory:low-stock:v{$version}:threshold:{$threshold}";
        if (Cache::has($key)) return Cache::get($key);
        $result = (function () use ($threshold) {
            $page = DB::table('stocks')->join('products', 'products.id', '=', 'stocks.product_id')
                ->join('warehouses', 'warehouses.id', '=', 'stocks.warehouse_id')
                ->whereRaw('(stocks.quantity - stocks.reserved_quantity) < ?', [$threshold])
                ->select('products.id as product_id', 'products.name as product_name', 'products.sku',
                    'warehouses.id as warehouse_id', 'warehouses.name as warehouse_name', 'stocks.quantity', 'stocks.reserved_quantity',
                    DB::raw('(stocks.quantity - stocks.reserved_quantity) as available_quantity'))
                ->orderBy('available_quantity')->paginate(50)->withQueryString();
            return ['page' => $page->toArray(), 'total' => $page->total()];
        })();
        if ($result['total'] > 0) Cache::put($key, $result, now()->addMinutes(2));
        return $result;
    }
}
