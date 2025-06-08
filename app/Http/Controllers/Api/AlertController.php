<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlertResource;
use App\Models\PriceAlert;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $query = PriceAlert::with(['product']);

        // Filter by email
        if ($request->has('email')) {
            $query->where('email', $request->get('email'));
        }

        $alerts = $query->get();

        return AlertResource::collection($alerts);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'email' => 'required|email',
            'condition' => ['required', Rule::in(['below', 'above', 'percent_drop', 'percent_increase'])],
            'target_price' => 'required_if:condition,below,above|numeric|min:0',
            'percent_threshold' => 'required_if:condition,percent_drop,percent_increase|numeric|min:0|max:100',
            'notification_channel' => ['required', Rule::in(['email', 'webhook', 'sms'])],
        ]);

        $alert = PriceAlert::create($validated)->fresh();
        $alert->load('product');

        return new AlertResource($alert);
    }

    public function show(int $id)
    {
        $alert = PriceAlert::find($id);

        if (!$alert) {
            return response()->json([
                'message' => 'Alert not found'
            ], 404);
        }

        $alert->load('product');
        return new AlertResource($alert);
    }

    public function update(Request $request, PriceAlert $alert)
    {
        $validated = $request->validate([
            'target_price' => 'sometimes|numeric|min:0',
            'percent_threshold' => 'sometimes|numeric|min:0|max:100',
            'is_active' => 'sometimes|boolean',
            'notification_channel' => ['sometimes', Rule::in(['email', 'webhook', 'sms'])],
        ]);

        $alert->update($validated);
        $alert->load('product');

        return new AlertResource($alert);
    }

    public function destroy(PriceAlert $alert)
    {
        $alert->delete();
        return response()->noContent();
    }

    public function shouldTrigger(PriceAlert $alert)
    {
        $product = $alert->product;
        $currentPrice = $product->getCurrentPrice();

        if (!$currentPrice) {
            return response()->json([
                'should_trigger' => false,
                'message' => 'No current price available',
                'target_price' => $alert->target_price,
                'condition' => $alert->condition
            ]);
        }

        $shouldTrigger = $this->checkTriggerCondition($alert, $currentPrice->price);

        return response()->json([
            'should_trigger' => $shouldTrigger,
            'current_price' => $currentPrice->price,
            'target_price' => $alert->target_price,
            'condition' => $alert->condition
        ]);
    }

    public function trigger(PriceAlert $alert)
    {
        $alert->increment('trigger_count');
        $alert->update(['last_triggered_at' => now()]);

        return response()->json([
            'message' => 'Alert triggered successfully',
            'data' => [
                'trigger_count' => $alert->trigger_count
            ]
        ]);
    }

    public function statistics()
    {
        $totalAlerts = PriceAlert::count();
        $activeAlerts = PriceAlert::where('is_active', true)->count();
        $triggeredAlerts = PriceAlert::where('trigger_count', '>', 0)->count();
        $triggerRate = $totalAlerts > 0 ? round(($triggeredAlerts / $totalAlerts) * 100, 2) : 0;

        return response()->json([
            'total_alerts' => $totalAlerts,
            'active_alerts' => $activeAlerts,
            'triggered_alerts' => $triggeredAlerts,
            'trigger_rate' => $triggerRate
        ]);
    }

    private function checkTriggerCondition(PriceAlert $alert, float $currentPrice): bool
    {
        return match ($alert->condition) {
            'below' => $currentPrice < $alert->target_price,
            'above' => $currentPrice > $alert->target_price,
            'percent_drop' => $this->checkPercentChange($alert, $currentPrice, 'drop'),
            'percent_increase' => $this->checkPercentChange($alert, $currentPrice, 'increase'),
            default => false,
        };
    }

    private function checkPercentChange(PriceAlert $alert, float $currentPrice, string $direction): bool
    {
        // For now, return false - would need historical price comparison
        // This would be implemented with proper business logic
        return false;
    }
}
