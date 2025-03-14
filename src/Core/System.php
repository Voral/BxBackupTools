<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Core;

interface System
{
    public function exec(string $command, &$output, &$resultCode): false|string;
}
