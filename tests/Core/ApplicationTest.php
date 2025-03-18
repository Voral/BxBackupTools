<?php

declare(strict_types=1);

namespace Core;

use PHPUnit\Framework\TestCase;
use Vasoft\BxBackupTools\Core\Application;
use Vasoft\BxBackupTools\Core\MessageContainer;
use Vasoft\BxBackupTools\Core\Task;

/**
 * @internal
 * @coversDefaultClass \Vasoft\BxBackupTools\Core\Application
 */
final class ApplicationTest extends TestCase
{
    /**
     * Все задачи оборачивают последнюю.
     */
    public function testHandle(): void
    {
        $app = new Application(
            [
                new TestHandler(1),
                new TestHandler(2),
                new TestTask(),
            ],
        );
        $messages = new MessageContainer();
        $app->handle($messages);
        $this->assertEquals([
            'Task 1 before',
            'Task 2 before',
            'Last task executed',
            'Task 2 after',
            'Task 1 after',
        ], $messages->getStringArray());
    }
}

class TestTask implements Task
{
    public function handle(MessageContainer $message, ?Task $next = null): void
    {
        $message->add('TestLastTask', 'Last task executed');
    }
}

class TestHandler implements Task
{
    public function __construct(private readonly int $index) {}

    public function handle(MessageContainer $message, ?Task $next = null): void
    {
        $message->add('TestTask' . $this->index, sprintf('Task %d before', $this->index));
        $next->handle($message);
        $message->add('TestTask' . $this->index, sprintf('Task %d after', $this->index));
    }
}
