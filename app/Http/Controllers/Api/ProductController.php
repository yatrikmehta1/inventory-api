<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ProductIndexRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function index(ProductIndexRequest $request, ProductService $service)
    {
        return ProductResource::collection($service->listing($request->validated()))
            ->response()->setStatusCode(Response::HTTP_OK);
    }
}
