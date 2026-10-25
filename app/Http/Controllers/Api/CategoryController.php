<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CategoryTreeService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    public function tree(CategoryTreeService $service): JsonResponse
    {
        $tree = $service->tree();
        if (empty($tree)) return response()->json(['data' => []], Response::HTTP_OK);
        return response()->json(['data' => $tree], Response::HTTP_OK);
    }
}
