<?php

namespace App\Services;

use App\DTOs\AlertDTO;
use App\Models\PriceAlert;
use App\Repositories\Contracts\AlertRepositoryInterface;
use App\Services\Contracts\AlertTriggerStrategyInterface;
use App\Services\AlertTrigger\AlertTriggerStrategyFactory;
use Illuminate\Database\Eloquent\Collection;

class AlertService
{
    public function __construct(
        private readonly AlertRepositoryInterface $alertRepository,
        private readonly AlertTriggerStrategyFactory $strategyFactory
    ) {}

    public function createAlert(AlertDTO $alertDTO): PriceAlert
    {
        return $this->alertRepository->create($alertDTO->toArray());
    }

    public function updateAlert(PriceAlert $alert, array $data): PriceAlert
    {
        return $this->alertRepository->update($alert, $data);
    }

    public function deleteAlert(PriceAlert $alert): bool
    {
        return $this->alertRepository->delete($alert);
    }

    public function getAlertsForUser(string $email): Collection
    {
        return $this->alertRepository->findByEmail($email);
    }

    public function shouldTrigger(PriceAlert $alert): array
    {
        $product = $alert->product;
        $currentPrice = $product->getCurrentPrice();

        if (!$currentPrice) {
            return [
                'should_trigger' => false,
                'message' => 'No current price available',
                'target_price' => $alert->target_price,
                'condition' => $alert->condition->value
            ];
        }

        $strategy = $this->strategyFactory->create($alert->condition->value);
        $shouldTrigger = $strategy->shouldTrigger($alert, $currentPrice);

        return [
            'should_trigger' => $shouldTrigger,
            'current_price' => $currentPrice->price,
            'target_price' => $alert->target_price,
            'previous_price' => $alert->previous_price,
            'condition' => $alert->condition->value,
            'message' => $shouldTrigger ? 'Alert conditions met' : 'Alert conditions not met'
        ];
    }

    public function triggerAlert(PriceAlert $alert): array
    {
        $alert->markAsTriggered();

        return [
            'message' => 'Alert triggered successfully',
            'trigger_count' => $alert->trigger_count,
            'last_triggered_at' => $alert->last_triggered_at
        ];
    }

    public function getAlertStatistics(): array
    {
        $totalAlerts = $this->alertRepository->getTotalCount();
        $activeAlerts = $this->alertRepository->getActiveCount();
        $triggeredAlerts = $this->alertRepository->getTriggeredCount();
        $triggerRate = $totalAlerts > 0 ? round(($triggeredAlerts / $totalAlerts) * 100, 2) : 0;

        return [
            'total_alerts' => $totalAlerts,
            'active_alerts' => $activeAlerts,
            'triggered_alerts' => $triggeredAlerts,
            'trigger_rate' => $triggerRate,
            'average_triggers_per_alert' => $this->alertRepository->getAverageTriggersPerAlert()
        ];
    }

    public function getActiveAlertsReadyToTrigger(int $minMinutesBetween = 60): Collection
    {
        return $this->alertRepository->getActiveAlertsReadyToTrigger($minMinutesBetween);
    }

    public function checkAndTriggerAlerts(): array
    {
        $readyAlerts = $this->getActiveAlertsReadyToTrigger();
        $triggered = [];
        $skipped = [];

        foreach ($readyAlerts as $alert) {
            $triggerCheck = $this->shouldTrigger($alert);

            if ($triggerCheck['should_trigger']) {
                $this->triggerAlert($alert);
                $triggered[] = $alert->id;
            } else {
                $skipped[] = $alert->id;
            }
        }

        return [
            'checked' => $readyAlerts->count(),
            'triggered' => count($triggered),
            'skipped' => count($skipped),
            'triggered_alert_ids' => $triggered,
            'skipped_alert_ids' => $skipped
        ];
    }
}
