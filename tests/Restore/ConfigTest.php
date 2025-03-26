<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Restore;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass \Vasoft\BxBackupTools\Restore\Config
 */
final class ConfigTest extends TestCase
{
    private array $defaultSettings = [
        'loggingEnabled' => true,
        'unpackEnabled' => false,
        'restoreFilesEnabled' => true,
        'restoreDatabaseEnabled' => false,
        'restoreCreditsEnabled' => true,
        'database' => [
            'host' => 'localhost',
            'name' => 'bitrix_db',
            'user' => 'root',
            'password' => 'password',
        ],
        'archive' => [
            'password' => 'archive_password',
            'path' => '/path/to/archive',
        ],
        'site' => [
            'documentRoot' => '/var/www/html',
        ],
    ];

    public function testIsLoggingEnabled(): void
    {
        // Проверяем, что возвращается значение из settings
        $config = new Config($this->defaultSettings);
        self::assertTrue($config->isLoggingEnabled());

        // Проверяем, что при отсутствии значения возвращается false
        $settingsWithoutLogging = [];
        $configWithoutLogging = new Config($settingsWithoutLogging);
        self::assertFalse($configWithoutLogging->isLoggingEnabled());
    }

    public function testIsUnpackEnabled(): void
    {
        // Проверяем, что возвращается значение из settings
        $config = new Config($this->defaultSettings);
        self::assertFalse($config->isUnpackEnabled());

        // Проверяем, что по умолчанию возвращается true
        $settingsWithoutUnpack = [];
        $configWithoutUnpack = new Config($settingsWithoutUnpack);
        self::assertTrue($configWithoutUnpack->isUnpackEnabled());
    }

    public function testIsRestoreFilesEnabled(): void
    {
        // Проверяем, что возвращается значение из settings
        $config = new Config($this->defaultSettings);
        self::assertTrue($config->isRestoreFilesEnabled());

        // Проверяем, что по умолчанию возвращается true
        $settingsWithoutRestoreFiles = [];
        $configWithoutRestoreFiles = new Config($settingsWithoutRestoreFiles);
        self::assertTrue($configWithoutRestoreFiles->isRestoreFilesEnabled());
    }

    public function testIsRestoreDatabaseEnabled(): void
    {
        // Проверяем, что возвращается значение из settings
        $config = new Config($this->defaultSettings);
        self::assertFalse($config->isRestoreDatabaseEnabled());

        // Проверяем, что по умолчанию возвращается true
        $settingsWithoutRestoreDatabase = [];
        $configWithoutRestoreDatabase = new Config($settingsWithoutRestoreDatabase);
        self::assertTrue($configWithoutRestoreDatabase->isRestoreDatabaseEnabled());
    }

    public function testIsRestoreCreditsEnabled(): void
    {
        // Проверяем, что возвращается значение из settings
        $config = new Config($this->defaultSettings);
        self::assertTrue($config->isRestoreCreditsEnabled());

        // Проверяем, что по умолчанию возвращается true
        $settingsWithoutRestoreCredits = [];
        $configWithoutRestoreCredits = new Config($settingsWithoutRestoreCredits);
        self::assertTrue($configWithoutRestoreCredits->isRestoreCreditsEnabled());
    }

    public function testGetDatabaseHost(): void
    {
        $config = new Config($this->defaultSettings);
        self::assertSame('localhost', $config->getDatabaseHost());

        // Проверяем, что при отсутствии значения возвращается пустая строка
        $settingsWithoutDatabaseHost = [];
        $configWithoutDatabaseHost = new Config($settingsWithoutDatabaseHost);
        self::assertSame('', $configWithoutDatabaseHost->getDatabaseHost());
    }

    public function testGetDatabaseName(): void
    {
        $config = new Config($this->defaultSettings);
        self::assertSame('bitrix_db', $config->getDatabaseName());

        // Проверяем, что при отсутствии значения возвращается пустая строка
        $settingsWithoutDatabaseName = [];
        $configWithoutDatabaseName = new Config($settingsWithoutDatabaseName);
        self::assertSame('', $configWithoutDatabaseName->getDatabaseName());
    }

    public function testGetDatabaseUser(): void
    {
        $config = new Config($this->defaultSettings);
        self::assertSame('root', $config->getDatabaseUser());

        // Проверяем, что при отсутствии значения возвращается пустая строка
        $settingsWithoutDatabaseUser = [];
        $configWithoutDatabaseUser = new Config($settingsWithoutDatabaseUser);
        self::assertSame('', $configWithoutDatabaseUser->getDatabaseUser());
    }

    public function testGetDatabasePassword(): void
    {
        $config = new Config($this->defaultSettings);
        self::assertSame('password', $config->getDatabasePassword());

        // Проверяем, что при отсутствии значения возвращается пустая строка
        $settingsWithoutDatabasePassword = [];
        $configWithoutDatabasePassword = new Config($settingsWithoutDatabasePassword);
        self::assertSame('', $configWithoutDatabasePassword->getDatabasePassword());
    }

    public function testGetArchivePassword(): void
    {
        $config = new Config($this->defaultSettings);
        self::assertSame('archive_password', $config->getArchivePassword());

        // Проверяем, что при отсутствии значения возвращается пустая строка
        $settingsWithoutArchivePassword = [];
        $configWithoutArchivePassword = new Config($settingsWithoutArchivePassword);
        self::assertSame('', $configWithoutArchivePassword->getArchivePassword());
    }

    public function testGetArchivePath(): void
    {
        $config = new Config($this->defaultSettings);
        self::assertSame('/path/to/archive', $config->getArchivePath());

        // Проверяем, что при отсутствии значения возвращается пустая строка
        $settingsWithoutArchivePath = [];
        $configWithoutArchivePath = new Config($settingsWithoutArchivePath);
        self::assertSame('', $configWithoutArchivePath->getArchivePath());
    }

    public function testGetSiteDocumentRoot(): void
    {
        $config = new Config($this->defaultSettings);
        self::assertSame('/var/www/html', $config->getSiteDocumentRoot());

        // Проверяем, что при отсутствии значения возвращается пустая строка
        $settingsWithoutDocumentRoot = [];
        $configWithoutDocumentRoot = new Config($settingsWithoutDocumentRoot);
        self::assertSame('', $configWithoutDocumentRoot->getSiteDocumentRoot());
    }

    public function testGetCode(): void
    {
        $config = new Config($this->defaultSettings);
        self::assertSame('bitrixRestore', $config::getCode());
    }
}
