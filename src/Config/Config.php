<?php

namespace Vasoft\BxBackupTools\Config;

abstract class Config
{
    public function __construct(protected array $settings)
    {
    }

    abstract public static function getCode(): string;
}