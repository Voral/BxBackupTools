<?php

namespace Vasoft\BxBackupTools\Tasks;

use PHPUnit\Framework\TestCase;
use Vasoft\BxBackupTools\Core\Application;
use Vasoft\BxBackupTools\Core\MessageContainer;
use Vasoft\BxBackupTools\Core\Task;

class TimerTest extends TestCase
{
    /**
     * Должен вычислять время в секундах только в последующих задачах и добавлять информацию в сообщение
     * @dataProvider dataHandle
     */
    public function testHandle(int $time)
    {
        $app = new Application(
            [
                new TestTimerTask($time + 2),
                new Timer(),
                new TestTimerTask($time),
            ]
        );
        $messages = new MessageContainer();
        $app->handle($messages);
        $this->assertEquals(
            [sprintf('Execution time: %d sec', $time)],
            $messages->getStringArray(),
        );

    }

    public static function dataHandle(): array
    {
        return array([
            1, 3
        ]);
    }
}

class TestTimerTask implements Task
{
    public function __construct(
        private readonly int $sleepTime,
    )
    {
    }

    public function handle(MessageContainer $message, ?Task $next = null): void
    {
        sleep($this->sleepTime);
        $next?->handle($message, $next);
    }
}
