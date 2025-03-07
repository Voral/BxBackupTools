<?php

namespace Vasoft\BxBackupTools\Notifier\Telegram;

use Vasoft\BxBackupTools\Config\Config as BaseConfig;

class Config extends BaseConfig
{
    public function getToken(): string
    {
        return (string)($this->settings['token'] ?? '');
    }

    public function getChatId(): string
    {
        return (string)($this->settings['chatId'] ?? '');
    }

    public static function getCode(): string
    {
        return 'telegram';
    }
}