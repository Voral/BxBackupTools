<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Notifier\Telegram;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass \Vasoft\BxBackupTools\Notifier\Telegram\Config
 */
final class ConfigTest extends TestCase
{
    public function testGetToken(): void
    {
        $config = new Config(['token' => 'test-token']);
        self::assertSame('test-token', $config->getToken(), 'Token is not correct');
        $config = new Config([]);
        self::assertSame('', $config->getToken(), 'Default token value is not correct');
    }

    public function testGetChatId(): void
    {
        $config = new Config(['chatId' => 'test-chatId']);
        self::assertSame('test-chatId', $config->getChatId(), 'Chat ID is not correct');
        $config = new Config([]);
        self::assertSame('', $config->getToken(), 'Default chat ID value is not correct');
    }

    public function testGetCode(): void
    {
        $config = new Config([]);
        self::assertSame('telegram', $config->getCode(), 'Code is not correct');
    }
}
