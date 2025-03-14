<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Core;

interface Task
{
    public function handle(MessageContainer $message, ?self $next = null): void;
}
