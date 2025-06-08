<?php

namespace App\Models;

use App\Enums\AlertCondition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class PriceAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'email',
        'target_price',
        'condition',
        'percent_threshold',
        'notification_channel',
        'notification_config',
        'is_active',
        'last_triggered_at',
        'trigger_count'
    ];

    protected $casts = [
        'target_price' => 'decimal:2',
        'percent_threshold' => 'decimal:2',
        'notification_config' => 'array',
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime',
        'trigger_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'condition' => AlertCondition::class
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByEmail(Builder $query, string $email): Builder
    {
        return $query->where('email', $email);
    }

    public function scopeReadyToTrigger(Builder $query, int $minMinutesBetween = 60): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) use ($minMinutesBetween) {
                $q->whereNull('last_triggered_at')
                    ->orWhere('last_triggered_at', '<', now()->subMinutes($minMinutesBetween));
            });
    }

    // Helper methods
    public function shouldTrigger(PriceHistory $priceHistory): bool
    {
        if (!$this->is_active || !$priceHistory->is_available) {
            return false;
        }

        return match($this->condition) {
            AlertCondition::BELOW => $priceHistory->price < $this->target_price,
            AlertCondition::ABOVE => $priceHistory->price > $this->target_price,
            AlertCondition::EQUALS => abs($priceHistory->price - $this->target_price) < 0.01,
            AlertCondition::PERCENT_DROP => $this->checkPercentDrop($priceHistory),
            AlertCondition::PERCENT_INCREASE => $this->checkPercentIncrease($priceHistory),
            default => false
        };
    }

    private function checkPercentDrop(PriceHistory $priceHistory): bool
    {
        if (!$priceHistory->previous_price || !$this->percent_threshold) {
            return false;
        }

        $changePercent = $priceHistory->price_change_percent;
        return $changePercent !== null && $changePercent <= -$this->percent_threshold;
    }

    private function checkPercentIncrease(PriceHistory $priceHistory): bool
    {
        if (!$priceHistory->previous_price || !$this->percent_threshold) {
            return false;
        }

        $changePercent = $priceHistory->price_change_percent;
        return $changePercent !== null && $changePercent >= $this->percent_threshold;
    }

    public function markAsTriggered(): void
    {
        $this->update([
            'last_triggered_at' => now(),
            'trigger_count' => $this->trigger_count + 1
        ]);
    }

    public function getFormattedTargetPriceAttribute(): string
    {
        return number_format($this->target_price, 2) . ' PLN';
    }
}
