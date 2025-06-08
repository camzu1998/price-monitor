<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'description' => $this->description,
            'category' => $this->category,
            'image_url' => $this->image_url,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        // Include current price if sources are loaded
        if ($this->relationLoaded('sources')) {
            $currentPrice = $this->getCurrentPrice();
            if ($currentPrice) {
                $data['current_price'] = [
                    'price' => $currentPrice->price,
                    'currency' => $currentPrice->currency,
                    'source_name' => $currentPrice->productSource->name,
                    'scraped_at' => $currentPrice->scraped_at,
                ];
            }
        }

        return $data;
    }

    private function getCurrentPrice()
    {
        foreach ($this->sources as $source) {
            if ($source->priceHistories->isNotEmpty()) {
                return $source->priceHistories->first();
            }
        }
        return null;
    }
}
