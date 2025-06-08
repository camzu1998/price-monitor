<?php

namespace App\Repositories;

use App\Models\PriceAlert;
use App\Repositories\Contracts\AlertRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AlertRepository implements AlertRepositoryInterface
{
    public function create(array $data): PriceAlert
    {
        $alert = PriceAlert::create($data);
        $alert->load('product');

        return $alert;
    }

    public function update(PriceAlert $alert, array $data): PriceAlert
    {
        $alert->update($data);
        $alert->load('product');

        return $alert;
    }

    public function delete(PriceAlert $alert): bool
    {
        return $alert->delete();
    }

    public function findById(int $id): ?PriceAlert
    {
        return PriceAlert::with(['product'])->find($id);
    }

    public function findByEmail(string $email): Collection
    {
        return PriceAlert::with(['product'])
            ->where('email', $email)
            ->get();
    }

    public function getAll(): Collection
    {
        return PriceAlert::with(['product'])->get();
    }

    public function getActiveAlertsReadyToTrigger(int $minMinutesBetween = 60): Collection
    {
        return PriceAlert::with(['product'])
            ->readyToTrigger($minMinutesBetween)
            ->get();
    }

    public function getTotalCount(): int
    {
        return PriceAlert::count();
    }

    public function getActiveCount(): int
    {
        return PriceAlert::active()->count();
    }

    public function getTriggeredCount(): int
    {
        return PriceAlert::where('trigger_count', '>', 0)->count();
    }

    public function getAverageTriggersPerAlert(): float
    {
        $triggeredAlerts = $this->getTriggeredCount();

        if ($triggeredAlerts === 0) {
            return 0.0;
        }

        return round(
            PriceAlert::where('trigger_count', '>', 0)->avg('trigger_count'),
            2
        );
    }
}
