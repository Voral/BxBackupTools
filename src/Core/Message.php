<?php

namespace Vasoft\BxBackupTools\Core;

class Message
{
    public readonly int $time;

    public function __construct(
        public readonly string       $module,
        public readonly string|array $data,
    )
    {
        $this->time = time();
    }
}