<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'ILIKE', "%{$search}%")
                ->orWhere('description', 'ILIKE', "%{$search}%")
                ->orWhere('sku', 'ILIKE', "%{$search}%");
        }

        if ($request->has('category')) {
            $query->where('category', $request->get('category'));
        }

        $perPage = $request->get('per_page', 15);
        $products = $query->paginate($perPage);

        return ProductResource::collection($products);
    }

    public function show(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        if ($request->has('include') && str_contains($request->get('include'), 'current_price')) {
            $product->load(['sources.priceHistories' => function ($query) {
                $query->latest('scraped_at')->take(1);
            }]);
        }

        return new ProductResource($product);
    }
}
