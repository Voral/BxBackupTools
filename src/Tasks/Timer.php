<?php

namespace Vasoft\BxBackupTools\Tasks;

use Vasoft\BxBackupTools\Core\MessageContainer;
use Vasoft\BxBackupTools\Core\Task;

class Timer implements Task
{
    public const MODULE_ID = 'timer';

    public function handle(MessageContainer $message, ?Task $next = null): void
    {
        $time = microtime(true);
        $next->handle($message);
        $message->add(
            self::MODULE_ID,
            sprintf('Execution time: %d sec', microtime(true) - $time)
        );
    }
}