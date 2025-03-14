<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Core;

final class Message
{
    public readonly int $time;

    public function __construct(
        public readonly string $module,
        public readonly array|string $data,
    ) {
        $this->time = time();
    }
}
