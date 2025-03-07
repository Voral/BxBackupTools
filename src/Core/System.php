<?php

namespace Vasoft\BxBackupTools\Core;

interface System
{
    function exec(string $command, &$output, &$resultCode): string|false;
}