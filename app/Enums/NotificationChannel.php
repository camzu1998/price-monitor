<?php

namespace App\Enums;

enum NotificationChannel: string
{
    case EMAIL = 'email';
    case WEBHOOK = 'webhook';
    case SMS = 'sms';
    case SLACK = 'slack';
    case DISCORD = 'discord';
    case TELEGRAM = 'telegram';

    public function getLabel(): string
    {
        return match($this) {
            self::EMAIL => 'Email',
            self::WEBHOOK => 'Webhook',
            self::SMS => 'SMS',
            self::SLACK => 'Slack',
            self::DISCORD => 'Discord',
            self::TELEGRAM => 'Telegram',
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::EMAIL => '📧',
            self::WEBHOOK => '🔗',
            self::SMS => '📱',
            self::SLACK => '💬',
            self::DISCORD => '🎮',
            self::TELEGRAM => '✈️',
        };
    }

    public function requiresConfiguration(): bool
    {
        return match($this) {
            self::EMAIL => false,
            self::WEBHOOK => true,
            self::SMS => true,
            self::SLACK => true,
            self::DISCORD => true,
            self::TELEGRAM => true,
        };
    }

    public function getRequiredConfigFields(): array
    {
        return match($this) {
            self::EMAIL => [],
            self::WEBHOOK => ['url'],
            self::SMS => ['phone_number'],
            self::SLACK => ['webhook_url', 'channel'],
            self::DISCORD => ['webhook_url'],
            self::TELEGRAM => ['bot_token', 'chat_id'],
        };
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getLabels(): array
    {
        $labels = [];
        foreach (self::cases() as $case) {
            $labels[$case->value] = $case->getLabel();
        }
        return $labels;
    }
}
