<?php

namespace Vasoft\BxBackupTools\Core;

class SystemCmd implements System
{
    function exec(string $command, &$output, &$resultCode): string|false
    {
        return exec($command, $output, $resultCode);
    }
}