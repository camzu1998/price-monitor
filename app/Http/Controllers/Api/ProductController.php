<?php

namespace App\Http\Controllers\Api;

use App\DTOs\ProductSearchDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductSearchRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService
    ) {}

    public function index(ProductSearchRequest $request): AnonymousResourceCollection
    {
        $searchDTO = ProductSearchDTO::fromRequest($request->validated());
        $products = $this->productService->searchProducts($searchDTO);

        return ProductResource::collection($products);
    }

    public function show(int $id, ProductSearchRequest $request): ProductResource|JsonResponse
    {
        $include = $request->validated()['include'] ?? null;
        $product = $this->productService->findProductWithIncludes($id, $include);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        return new ProductResource($product);
    }
}
