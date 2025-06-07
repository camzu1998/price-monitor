<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'description',
        'category',
        'image_url',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function sources(): HasMany
    {
        return $this->hasMany(ProductSource::class);
    }

    public function activeSources(): HasMany
    {
        return $this->hasMany(ProductSource::class)->where('is_active', true);
    }

    public function priceHistory(): HasManyThrough
    {
        return $this->hasManyThrough(PriceHistory::class, ProductSource::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(PriceAlert::class);
    }

    public function activeAlerts(): HasMany
    {
        return $this->hasMany(PriceAlert::class)->where('is_active', true);
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'ILIKE', "%{$term}%")
                ->orWhere('description', 'ILIKE', "%{$term}%")
                ->orWhere('sku', 'ILIKE', "%{$term}%");
        });
    }

    // Helper methods
    public function getCurrentPrice(?string $sourceName = null): ?PriceHistory
    {
        $query = $this->priceHistory()->latest('scraped_at');

        if ($sourceName) {
            $query->whereHas('productSource', fn($q) => $q->where('source_name', $sourceName));
        }

        return $query->first();
    }

    public function getLowestPrice(): ?PriceHistory
    {
        return $this->priceHistory()
            ->where('is_available', true)
            ->orderBy('price')
            ->first();
    }

    public function getHighestPrice(): ?PriceHistory
    {
        return $this->priceHistory()
            ->where('is_available', true)
            ->orderBy('price', 'desc')
            ->first();
    }

    public function getPriceTrend(int $days = 7): string
    {
        $recentPrices = $this->priceHistory()
            ->where('scraped_at', '>=', now()->subDays($days))
            ->orderBy('scraped_at')
            ->get();

        if ($recentPrices->count() < 2) {
            return 'stable';
        }

        $first = $recentPrices->first();
        $last = $recentPrices->last();

        if ($last->price < $first->price * 0.95) return 'down';
        if ($last->price > $first->price * 1.05) return 'up';

        return 'stable';
    }

    public function getAveragePrice(int $days = 30): ?float
    {
        return $this->priceHistory()
            ->where('scraped_at', '>=', now()->subDays($days))
            ->where('is_available', true)
            ->avg('price');
    }
}
