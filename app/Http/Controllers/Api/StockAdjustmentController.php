<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StockAdjustmentRequest;
use App\Http\Resources\StockMovementResource;
use App\Services\StockAdjustmentService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class StockAdjustmentController extends Controller
{
    public function store(StockAdjustmentRequest $request, StockAdjustmentService $service): JsonResponse
    {
        $result = $service->adjust($request->validated());
        return response()->json([
            'message' => 'Stock adjusted successfully.',
            'data' => [
                'product_id' => $result['stock']->product_id,
                'warehouse_id' => $result['stock']->warehouse_id,
                'quantity' => $result['stock']->quantity,
                'reserved_quantity' => $result['stock']->reserved_quantity,
                'available_quantity' => $result['stock']->quantity - $result['stock']->reserved_quantity,
                'movement' => (new StockMovementResource($result['movement']))->resolve(),
            ],
        ], Response::HTTP_CREATED);
    }
}
