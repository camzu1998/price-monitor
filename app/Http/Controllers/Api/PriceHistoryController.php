<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PriceHistoryResource;
use App\Models\Product;
use App\Models\PriceHistory;
use Illuminate\Http\Request;

class PriceHistoryController extends Controller
{
    public function index(Request $request, Product $product)
    {
        $query = PriceHistory::query()
            ->whereHas('productSource', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })
            ->with(['productSource'])
            ->orderBy('scraped_at', 'desc');

        // Filter by date range
        if ($request->has('from')) {
            $query->whereDate('scraped_at', '>=', $request->get('from'));
        }

        if ($request->has('to')) {
            $query->whereDate('scraped_at', '<=', $request->get('to'));
        }

        // Filter by source
        if ($request->has('source')) {
            $sourceName = $request->get('source');
            $query->whereHas('productSource', function ($query) use ($sourceName) {
                $query->where('source_name', 'ILIKE', "%{$sourceName}%");
            });
        }

        $priceHistory = $query->get();

        return PriceHistoryResource::collection($priceHistory);
    }
}
