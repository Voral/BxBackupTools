<?php

namespace Vasoft\BxBackupTools\Core;


class Application implements Task
{
    /**
     * @param array $handlers
     */
    public function __construct(
        private array $handlers
    )
    {
    }

    public function handle(MessageContainer $message, ?Task $next = null): void
    {
        $handlers = array_shift($this->handlers);
        $handlers?->handle($message, $this);
    }
}