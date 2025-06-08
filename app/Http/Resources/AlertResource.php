<?php

namespace App\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlertResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'email' => $this->email,
            'target_price' => $this->target_price,
            'condition' => $this->condition,
            'percent_threshold' => $this->percent_threshold,
            'notification_channel' => $this->notification_channel,
            'is_active' => $this->is_active,
            'last_triggered_at' => $this->last_triggered_at,
            'trigger_count' => $this->trigger_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'product' => $this->whenLoaded('product', function () {
                return [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'sku' => $this->product->sku,
                ];
            }),
        ];
    }

    public function withResponse(Request $request, JsonResponse $response)
    {
        if ($request->method() === 'POST') {
            $response->setStatusCode(201);
        }
    }
}
