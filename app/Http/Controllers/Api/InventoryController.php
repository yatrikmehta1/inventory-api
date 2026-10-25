<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\InventoryReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class InventoryController extends Controller
{
    public function summary(InventoryReportService $service): JsonResponse
    {
        return response()->json(['data' => $service->summary()], Response::HTTP_OK);
    }

    public function lowStock(Request $request, InventoryReportService $service): JsonResponse
    {
        $validated = $request->validate(['threshold' => ['sometimes', 'integer', 'min:0', 'max:2147483647', 'nullable']]);
        $threshold = (int)($validated['threshold'] ?? 10);
        $result = $service->lowStock($threshold);
        return response()->json(['data' => $result['page']], Response::HTTP_OK);
    }
}
