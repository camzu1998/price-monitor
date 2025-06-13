<?php

namespace App\Http\Controllers\Api;

use App\DTOs\PriceHistoryFilterDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\PriceHistoryResource;
use App\Models\Product;
use App\Services\PriceHistoryService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PriceHistoryController extends Controller
{
    public function __construct(
        private readonly PriceHistoryService $priceHistoryService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/products/{product}/price-history",
     *     operationId="getProductPriceHistory",
     *     tags={"Price History"},
     *     summary="Get product price history",
     *     description="Get price history for a specific product with optional filters",
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="from",
     *         in="query",
     *         description="Start date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-06-01")
     *     ),
     *     @OA\Parameter(
     *         name="to",
     *         in="query",
     *         description="End date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-06-12")
     *     ),
     *     @OA\Parameter(
     *         name="source",
     *         in="query",
     *         description="Filter by source name",
     *         required=false,
     *         @OA\Schema(type="string", example="amazon")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Maximum number of records to return",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=1000, default=100)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Price history list",
     *         @OA\JsonContent(ref="#/components/schemas/PriceHistoryCollection")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function index(Request $request, Product $product): AnonymousResourceCollection
    {
        $filterDTO = PriceHistoryFilterDTO::fromRequest($product->id, $request->all());
        $priceHistory = $this->priceHistoryService->getProductPriceHistory($filterDTO);

        return PriceHistoryResource::collection($priceHistory);
    }
}
