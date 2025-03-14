<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Restore;

use Vasoft\BxBackupTools\Config\Config as BaseConfig;

class Config extends BaseConfig
{
    public function __construct(
        array $settings,
    ) {
        parent::__construct($settings);
    }

    public static function getCode(): string
    {
        return 'bitrixRestore';
    }

    public function isLoggingEnabled(): bool
    {
        return (bool) ($this->settings['loggingEnabled'] ?? false);
    }

    public function isUnpackEnabled(): bool
    {
        return (bool) ($this->settings['unpackEnabled'] ?? true);
    }

    public function isRestoreFilesEnabled(): bool
    {
        return (bool) ($this->settings['restoreFilesEnabled'] ?? true);
    }

    public function isRestoreDatabaseEnabled(): bool
    {
        return (bool) ($this->settings['restoreDatabaseEnabled'] ?? true);
    }

    public function isRestoreCreditsEnabled(): bool
    {
        return (bool) ($this->settings['restoreCreditsEnabled'] ?? true);
    }

    public function getDatabaseHost(): string
    {
        return (string) ($this->settings['database']['host'] ?? '');
    }

    public function getDatabaseName(): string
    {
        return (string) ($this->settings['database']['name'] ?? '');
    }

    public function getDatabaseUser(): string
    {
        return (string) ($this->settings['database']['user'] ?? '');
    }

    public function getDatabasePassword(): string
    {
        return (string) ($this->settings['database']['password'] ?? '');
    }

    public function getArchivePassword(): string
    {
        return (string) ($this->settings['archive']['password'] ?? '');
    }

    public function getArchivePath(): string
    {
        return (string) ($this->settings['archive']['path'] ?? '');
    }

    public function getSiteDocumentRoot(): string
    {
        return (string) ($this->settings['site']['documentRoot'] ?? '');
    }
}
