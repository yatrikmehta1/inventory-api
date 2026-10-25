<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StockAdjustmentController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\ProductMovementController;
use Symfony\Component\HttpFoundation\Response;

Route::get('/user', function (Request $request) {
    return response()->json($request->user(), Response::HTTP_OK);
})->middleware('auth:sanctum');

Route::get('/categories/tree', [CategoryController::class, 'tree']);
Route::get('/products', [ProductController::class, 'index']);
Route::post('/stock/adjust', [StockAdjustmentController::class, 'store']);
Route::get('/inventory/summary', [InventoryController::class, 'summary']);
Route::get('/inventory/low-stock', [InventoryController::class, 'lowStock']);
Route::get('/products/{product}/movements', [ProductMovementController::class, 'index']);
