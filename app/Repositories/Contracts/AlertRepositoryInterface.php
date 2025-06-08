<?php

namespace App\Repositories\Contracts;

use App\Models\PriceAlert;
use Illuminate\Database\Eloquent\Collection;

interface AlertRepositoryInterface
{
    public function create(array $data): PriceAlert;

    public function update(PriceAlert $alert, array $data): PriceAlert;

    public function delete(PriceAlert $alert): bool;

    public function findById(int $id): ?PriceAlert;

    public function findByEmail(string $email): Collection;

    public function getAll(): Collection;

    public function getActiveAlertsReadyToTrigger(int $minMinutesBetween = 60): Collection;

    public function getTotalCount(): int;

    public function getActiveCount(): int;

    public function getTriggeredCount(): int;

    public function getAverageTriggersPerAlert(): float;
}
