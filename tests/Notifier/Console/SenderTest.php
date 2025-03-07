<?php

namespace Notifier\Console;

use Vasoft\BxBackupTools\Core\Message;
use Vasoft\BxBackupTools\Core\MessageContainer;
use Vasoft\BxBackupTools\Notifier\Console\Sender;
use Vasoft\BxBackupTools\Core\Task;
use PHPUnit\Framework\TestCase;

class SenderTest extends TestCase
{
    public function testHandle()
    {
        $message = new MessageContainer();
        $sender = new Sender();
        ob_start();
        $sender->handle($message, new TestHandler());
        $content = ob_get_clean();
        $this->assertEquals("executed" . PHP_EOL . "two lines" . PHP_EOL, $content, 'Wrong output');
        $this->assertEquals(['executed', 'two lines'], $message->getStringArray(), 'Message modified');
    }
}

class TestHandler implements \Vasoft\BxBackupTools\Core\Task
{
    public function handle(MessageContainer $message, ?Task $next = null): void
    {
        $message->add("handler", ["executed", "two lines"]);
    }
}