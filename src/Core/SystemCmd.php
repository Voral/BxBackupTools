<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Core;

final class SystemCmd implements System
{
    public function exec(string $command, &$output, &$resultCode): false|string
    {
        return exec($command, $output, $resultCode);
    }
}
