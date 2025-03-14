<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Notifier\Console;

use Vasoft\BxBackupTools\Core\MessageContainer;
use Vasoft\BxBackupTools\Core\Task;

final class Sender implements Task
{
    public function handle(MessageContainer $message, ?Task $next = null): void
    {
        $next->handle($message);
        $this->push($this->render($message->getStringArray()));
    }

    private function push(string $message): void
    {
        echo $message, PHP_EOL;
    }

    private function render(array $messageStrings): string
    {
        $message = implode(PHP_EOL, $messageStrings);
        $message = preg_replace('#<br\s*/?>#i', PHP_EOL, $message);

        return strip_tags($message);
    }
}
