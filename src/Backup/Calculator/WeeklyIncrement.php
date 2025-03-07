<?php

namespace Vasoft\BxBackupTools\Backup\Calculator;

class WeeklyIncrement implements PathCalculator
{

    public function getNext(string $path): string
    {
        return $path . date('w');
    }
}