<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Config;

final class Container
{
    private array $config = [];

    public function __construct(private readonly array $settings) {}

    public function get(string $configClass): Config
    {
        $code = $configClass::getCode();
        if (!isset($this->settings[$code])) {
            throw new \InvalidArgumentException("Config with code {$code} not found");
        }
        if (isset($this->config[$code])) {
            return $this->config[$code];
        }
        $this->config[$code] = new $configClass($this->settings[$code]);

        return $this->config[$code];
    }
}
