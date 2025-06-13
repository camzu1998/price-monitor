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

    /**
     * @OA\Get(
     *     path="/api/products",
     *     operationId="getProducts",
     *     tags={"Products"},
     *     summary="Get products list",
     *     description="Get paginated list of products with optional filters",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for product name, description or SKU",
     *         required=false,
     *         @OA\Schema(type="string", example="iPhone")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filter by category",
     *         required=false,
     *         @OA\Schema(type="string", example="Electronics")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=15)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, default=1)
     *     ),
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         description="Include related data (current_price, alerts)",
     *         required=false,
     *         @OA\Schema(type="string", example="current_price,alerts")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sort field",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"name", "created_at", "updated_at", "price"},
     *             default="created_at"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort_direction",
     *         in="query",
     *         description="Sort direction",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, default="desc")
     *     ),
     *     @OA\Parameter(
     *         name="is_active",
     *         in="query",
     *         description="Filter by active status",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Products list",
     *         @OA\JsonContent(ref="#/components/schemas/ProductCollection")
     *     )
     * )
     */
    public function index(ProductSearchRequest $request): AnonymousResourceCollection
    {
        $searchDTO = ProductSearchDTO::fromRequest($request->validated());
        $products = $this->productService->searchProducts($searchDTO);

        return ProductResource::collection($products);
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     operationId="getProduct",
     *     tags={"Products"},
     *     summary="Get product details",
     *     description="Get details of a specific product",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         description="Include related data (current_price)",
     *         required=false,
     *         @OA\Schema(type="string", example="current_price")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product details",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(ref="#/components/schemas/ProductResource"),
     *                 @OA\Schema(ref="#/components/schemas/ProductWithPriceResource")
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product not found")
     *         )
     *     )
     * )
     */
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
