<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StockMovementResource;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ProductMovementController extends Controller
{
    public function index(Request $request, Product $product)
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'], 'date_to' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        if (!empty($filters['date_from']) && !empty($filters['date_to']) && $filters['date_from'] > $filters['date_to']) {
            throw ValidationException::withMessages(['date_to' => 'date_to must be on or after date_from.']);
        }
        $base = StockMovement::query()->where('product_id', $product->id)
            ->when(!empty($filters['date_from']), fn ($q) => $q->where('moved_at', '>=', $filters['date_from'].' 00:00:00'))
            ->when(!empty($filters['date_to']), fn ($q) => $q->where('moved_at', '<', date('Y-m-d', strtotime($filters['date_to'].' +1 day')).' 00:00:00'));
        $summary = (clone $base)->selectRaw("COALESCE(SUM(CASE WHEN movement_type = 'stock_in' THEN quantity ELSE 0 END), 0) as total_units_in")
            ->selectRaw("COALESCE(SUM(CASE WHEN movement_type = 'stock_out' THEN quantity ELSE 0 END), 0) as total_units_out")
            ->selectRaw("COALESCE(SUM(CASE WHEN movement_type = 'stock_in' THEN quantity WHEN movement_type = 'stock_out' THEN -quantity ELSE 0 END), 0) as net_movement")
            ->first();
        // Use the Eloquent model so its moved_at datetime cast is applied
        // before StockMovementResource formats it as ISO 8601.
        $movements = (clone $base)->orderByDesc('moved_at')->orderByDesc('id')
            ->paginate((int)($filters['per_page'] ?? 20))->withQueryString();
        return response()->json([
            'summary' => ['total_units_in' => (int)$summary->total_units_in, 'total_units_out' => (int)$summary->total_units_out, 'net_movement' => (int)$summary->net_movement],
            'data' => StockMovementResource::collection($movements)->response()->getData(true)['data'],
            'meta' => ['current_page' => $movements->currentPage(), 'last_page' => $movements->lastPage(), 'per_page' => $movements->perPage(), 'total' => $movements->total()],
        ], Response::HTTP_OK);
    }
}
