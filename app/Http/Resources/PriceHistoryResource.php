<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'price' => $this->price,
            'previous_price' => $this->previous_price,
            'currency' => $this->currency,
            'is_available' => $this->is_available,
            'price_change' => $this->price_change,
            'price_change_percent' => $this->price_change_percent,
            'source_name' => $this->productSource->name,
            'scraped_at' => $this->scraped_at,
        ];
    }
}
