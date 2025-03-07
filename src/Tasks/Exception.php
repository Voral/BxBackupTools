<?php

namespace Vasoft\BxBackupTools\Tasks;

use Vasoft\BxBackupTools\Core\Exceptions\ProcessException;
use Vasoft\BxBackupTools\Core\MessageContainer;
use Vasoft\BxBackupTools\Core\Task;

class Exception implements Task
{

    /** @noinspection PhpRedundantCatchClauseInspection */
    public function handle(MessageContainer $message, ?Task $next = null): void
    {
        try {
            $next->handle($message);
        } catch (ProcessException $e) {
            $messages = [
                $e->getMessage()
            ];
            if (is_array($e->data)) {
                $messages = array_merge($messages, $e->data);
            } else {
                $messages[] = $e->data;
            }
            $message->add($e->module, $messages);
        }
    }
}