<?php

namespace Vasoft\BxBackupTools\Notifier\Telegram;

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{

    public function testGetToken()
    {
        $config = new Config(['token' => 'test-token']);
        $this->assertEquals('test-token', $config->getToken(), 'Token is not correct');
        $config = new Config([]);
        $this->assertEquals('', $config->getToken(), 'Default token value is not correct');
    }

    public function testGetChatId()
    {
        $config = new Config(['chatId' => 'test-chatId']);
        $this->assertEquals('test-chatId', $config->getChatId(), 'Chat ID is not correct');
        $config = new Config([]);
        $this->assertEquals('', $config->getToken(), 'Default chat ID value is not correct');
    }

    public function testGetCode()
    {
        $config = new Config([]);
        $this->assertEquals('telegram', $config->getCode(), 'Code is not correct');
    }
}
