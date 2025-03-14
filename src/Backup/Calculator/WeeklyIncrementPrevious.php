<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Backup\Calculator;

final class WeeklyIncrementPrevious implements PathCalculator
{
    public function getNext(string $path): string
    {
        $day = -1 + (int) date('w');
        if ($day < 0) {
            $day = 6;
        }

        return $path . $day;
    }
}
