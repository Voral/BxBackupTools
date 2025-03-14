<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Backup\Calculator;

interface PathCalculator
{
    public function getNext(string $path): string;
}
