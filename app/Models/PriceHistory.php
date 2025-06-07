<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class PriceHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_source_id',
        'price',
        'previous_price',
        'currency',
        'is_available',
        'raw_data',
        'metadata',
        'scraped_at'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'previous_price' => 'decimal:2',
        'is_available' => 'boolean',
        'raw_data' => 'array',
        'metadata' => 'array',
        'scraped_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function productSource(): BelongsTo
    {
        return $this->belongsTo(ProductSource::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_source_id', 'id')
            ->through('productSource');
    }

    // Scopes
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }

    public function scopeRecent(Builder $query, int $hours = 24): Builder
    {
        return $query->where('scraped_at', '>=', now()->subHours($hours));
    }

    public function scopeByCurrency(Builder $query, string $currency): Builder
    {
        return $query->where('currency', $currency);
    }

    public function scopePriceDrops(Builder $query, float $minPercent = 5): Builder
    {
        return $query->whereNotNull('previous_price')
            ->whereRaw('price < previous_price * ?', [1 - ($minPercent / 100)]);
    }

    // Computed attributes
    public function getPriceChangeAttribute(): ?float
    {
        if (!$this->previous_price) return null;
        return $this->price - $this->previous_price;
    }

    public function getPriceChangePercentAttribute(): ?float
    {
        if (!$this->previous_price || $this->previous_price == 0) return null;
        return (($this->price - $this->previous_price) / $this->previous_price) * 100;
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 2) . ' ' . $this->currency;
    }

    public function getFormattedPriceChangeAttribute(): ?string
    {
        $change = $this->price_change;
        if ($change === null) return null;

        $sign = $change >= 0 ? '+' : '';
        return $sign . number_format($change, 2) . ' ' . $this->currency;
    }

    // Helper methods
    public function hasPriceDropped(float $minPercent = 0): bool
    {
        if (!$this->previous_price) return false;
        $changePercent = $this->price_change_percent;
        return $changePercent !== null && $changePercent < -$minPercent;
    }

    public function hasPriceIncreased(float $minPercent = 0): bool
    {
        if (!$this->previous_price) return false;
        $changePercent = $this->price_change_percent;
        return $changePercent !== null && $changePercent > $minPercent;
    }
}
