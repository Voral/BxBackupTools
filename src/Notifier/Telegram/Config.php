<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Notifier\Telegram;

use Vasoft\BxBackupTools\Config\Config as BaseConfig;

final class Config extends BaseConfig
{
    public static function getCode(): string
    {
        return 'telegram';
    }

    public function getToken(): string
    {
        return (string) ($this->settings['token'] ?? '');
    }

    public function getChatId(): string
    {
        return (string) ($this->settings['chatId'] ?? '');
    }
}
