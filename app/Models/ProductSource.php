<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class ProductSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'source_name',
        'source_url',
        'price_selector',
        'availability_selector',
        'scraper_config',
        'is_active',
        'last_scraped_at'
    ];

    protected $casts = [
        'scraper_config' => 'array',
        'is_active' => 'boolean',
        'last_scraped_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function priceHistories(): HasMany
    {
        return $this->hasMany(PriceHistory::class);
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeBySource(Builder $query, string $sourceName): Builder
    {
        return $query->where('source_name', $sourceName);
    }

    public function scopeNeedsScraping(Builder $query, int $minutesOld = 60): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) use ($minutesOld) {
                $q->whereNull('last_scraped_at')
                    ->orWhere('last_scraped_at', '<', now()->subMinutes($minutesOld));
            });
    }

    // Helper methods
    public function getLatestPrice(): ?PriceHistory
    {
        return $this->priceHistory()->latest('scraped_at')->first();
    }

    public function getPriceChangeSince(int $hours = 24): ?float
    {
        $current = $this->getLatestPrice();
        if (!$current) return null;

        $previous = $this->priceHistory()
            ->where('scraped_at', '<=', now()->subHours($hours))
            ->latest('scraped_at')
            ->first();

        if (!$previous) return null;

        return $current->price - $previous->price;
    }

    public function updateLastScrapedAt(): void
    {
        $this->update(['last_scraped_at' => now()]);
    }

    public function getDomainAttribute(): string
    {
        return parse_url($this->source_url, PHP_URL_HOST) ?? '';
    }
}
