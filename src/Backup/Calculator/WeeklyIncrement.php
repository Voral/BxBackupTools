<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Backup\Calculator;

final class WeeklyIncrement implements PathCalculator
{
    public function getNext(string $path): string
    {
        return $path . date('w');
    }
}
