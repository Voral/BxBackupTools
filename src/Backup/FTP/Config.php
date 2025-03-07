<?php

namespace Vasoft\BxBackupTools\Backup\FTP;

use Vasoft\BxBackupTools\Backup\Calculator\PathCalculator;
use Vasoft\BxBackupTools\Config\Config as BaseConfig;

class Config extends BaseConfig
{
    protected ?PathCalculator $remotePathCalculator = null;
    protected ?PathCalculator $localPathCalculator = null;

    public function __construct(
        array $settings,
    )
    {
        parent::__construct($settings);
    }

    public function setRemotePathCalculator(PathCalculator $calculator): static{
        $this->remotePathCalculator = $calculator;
        return $this;
    }
    public function setLocalPathCalculator(PathCalculator $calculator): static{
        $this->localPathCalculator = $calculator;
        return $this;
    }

    public function getRemoteHost(): string
    {
        return $this->settings['remote']['host'] ?? '';
    }

    public function getRemoteProtocol(): string
    {
        return $this->settings['remote']['protocol'] ?? 'ftp';
    }

    public function getRemotePath(): string
    {
        $path = $this->settings['remote']['path'] ?? '';
        return $this->remotePathCalculator ? $this->remotePathCalculator->getNext($path) : $path;
    }

    public function getRemoteUser(): string
    {
        return $this->settings['remote']['user'] ?? '';
    }

    public function getRemotePassword(): string
    {
        return $this->settings['remote']['password'] ?? '';
    }

    public function getLocalPath(): string
    {
        $path = $this->settings['local']['path'] ?? '';
        return $this->localPathCalculator ? $this->localPathCalculator->getNext($path) : $path;
    }

    public function getMirrorParallels(): int
    {
        return $this->settings['mirror']['parallel'] ?? 0;
    }

    public function getMirrorDelete(): bool
    {
        return $this->settings['mirror']['delete'] ?? false;
    }

    public static function getCode(): string
    {
        return 'backupFTP';
    }
}