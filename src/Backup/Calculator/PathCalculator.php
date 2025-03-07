<?php

namespace Vasoft\BxBackupTools\Backup\Calculator;

interface PathCalculator
{
    public function getNext(string $path): string;
}