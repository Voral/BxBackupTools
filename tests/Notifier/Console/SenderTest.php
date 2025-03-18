<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Notifier\Console;

use PHPUnit\Framework\TestCase;
use Vasoft\BxBackupTools\Core\MessageContainer;
use Vasoft\BxBackupTools\Core\Task;

/**
 * @internal
 * @coversDefaultClass \Vasoft\BxBackupTools\Notifier\Console\Sender
 */
final class SenderTest extends TestCase
{
    public function testHandle(): void
    {
        $message = new MessageContainer();
        $sender = new Sender();
        ob_start();
        $sender->handle($message, new TestHandler());
        $content = ob_get_clean();
        $this->assertEquals('executed' . PHP_EOL . 'two lines' . PHP_EOL, $content, 'Wrong output');
        $this->assertEquals(['executed', 'two lines'], $message->getStringArray(), 'Message modified');
    }
}

class TestHandler implements Task
{
    public function handle(MessageContainer $message, ?Task $next = null): void
    {
        $message->add('handler', ['executed', 'two lines']);
    }
}
