<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Core;

final class Application implements Task
{
    public function __construct(
        private array $handlers,
    ) {}

    public function handle(MessageContainer $message, ?Task $next = null): void
    {
        $handlers = array_shift($this->handlers);
        $handlers?->handle($message, $this);
    }
}
