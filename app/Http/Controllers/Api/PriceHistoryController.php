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

    public function index(Request $request, Product $product): AnonymousResourceCollection
    {
        $filterDTO = PriceHistoryFilterDTO::fromRequest($product->id, $request->all());
        $priceHistory = $this->priceHistoryService->getProductPriceHistory($filterDTO);

        return PriceHistoryResource::collection($priceHistory);
    }
}
