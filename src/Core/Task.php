<?php

namespace Vasoft\BxBackupTools\Core;

interface Task
{
    public function handle(MessageContainer $message, ?Task $next = null): void;
}