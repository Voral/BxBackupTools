<?php

namespace Vasoft\BxBackupTools\Restore;

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
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
        $this->assertTrue($config->isLoggingEnabled());

        // Проверяем, что при отсутствии значения возвращается false
        $settingsWithoutLogging = [];
        $configWithoutLogging = new Config($settingsWithoutLogging);
        $this->assertFalse($configWithoutLogging->isLoggingEnabled());
    }

    public function testIsUnpackEnabled(): void
    {
        // Проверяем, что возвращается значение из settings
        $config = new Config($this->defaultSettings);
        $this->assertFalse($config->isUnpackEnabled());

        // Проверяем, что по умолчанию возвращается true
        $settingsWithoutUnpack = [];
        $configWithoutUnpack = new Config($settingsWithoutUnpack);
        $this->assertTrue($configWithoutUnpack->isUnpackEnabled());
    }

    public function testIsRestoreFilesEnabled(): void
    {
        // Проверяем, что возвращается значение из settings
        $config = new Config($this->defaultSettings);
        $this->assertTrue($config->isRestoreFilesEnabled());

        // Проверяем, что по умолчанию возвращается true
        $settingsWithoutRestoreFiles = [];
        $configWithoutRestoreFiles = new Config($settingsWithoutRestoreFiles);
        $this->assertTrue($configWithoutRestoreFiles->isRestoreFilesEnabled());
    }

    public function testIsRestoreDatabaseEnabled(): void
    {
        // Проверяем, что возвращается значение из settings
        $config = new Config($this->defaultSettings);
        $this->assertFalse($config->isRestoreDatabaseEnabled());

        // Проверяем, что по умолчанию возвращается true
        $settingsWithoutRestoreDatabase = [];
        $configWithoutRestoreDatabase = new Config($settingsWithoutRestoreDatabase);
        $this->assertTrue($configWithoutRestoreDatabase->isRestoreDatabaseEnabled());
    }

    public function testIsRestoreCreditsEnabled(): void
    {
        // Проверяем, что возвращается значение из settings
        $config = new Config($this->defaultSettings);
        $this->assertTrue($config->isRestoreCreditsEnabled());

        // Проверяем, что по умолчанию возвращается true
        $settingsWithoutRestoreCredits = [];
        $configWithoutRestoreCredits = new Config($settingsWithoutRestoreCredits);
        $this->assertTrue($configWithoutRestoreCredits->isRestoreCreditsEnabled());
    }

    public function testGetDatabaseHost(): void
    {
        $config = new Config($this->defaultSettings);
        $this->assertEquals('localhost', $config->getDatabaseHost());

        // Проверяем, что при отсутствии значения возвращается пустая строка
        $settingsWithoutDatabaseHost = [];
        $configWithoutDatabaseHost = new Config($settingsWithoutDatabaseHost);
        $this->assertEquals('', $configWithoutDatabaseHost->getDatabaseHost());
    }

    public function testGetDatabaseName(): void
    {
        $config = new Config($this->defaultSettings);
        $this->assertEquals('bitrix_db', $config->getDatabaseName());

        // Проверяем, что при отсутствии значения возвращается пустая строка
        $settingsWithoutDatabaseName = [];
        $configWithoutDatabaseName = new Config($settingsWithoutDatabaseName);
        $this->assertEquals('', $configWithoutDatabaseName->getDatabaseName());
    }

    public function testGetDatabaseUser(): void
    {
        $config = new Config($this->defaultSettings);
        $this->assertEquals('root', $config->getDatabaseUser());

        // Проверяем, что при отсутствии значения возвращается пустая строка
        $settingsWithoutDatabaseUser = [];
        $configWithoutDatabaseUser = new Config($settingsWithoutDatabaseUser);
        $this->assertEquals('', $configWithoutDatabaseUser->getDatabaseUser());
    }

    public function testGetDatabasePassword(): void
    {
        $config = new Config($this->defaultSettings);
        $this->assertEquals('password', $config->getDatabasePassword());

        // Проверяем, что при отсутствии значения возвращается пустая строка
        $settingsWithoutDatabasePassword = [];
        $configWithoutDatabasePassword = new Config($settingsWithoutDatabasePassword);
        $this->assertEquals('', $configWithoutDatabasePassword->getDatabasePassword());
    }

    public function testGetArchivePassword(): void
    {
        $config = new Config($this->defaultSettings);
        $this->assertEquals('archive_password', $config->getArchivePassword());

        // Проверяем, что при отсутствии значения возвращается пустая строка
        $settingsWithoutArchivePassword = [];
        $configWithoutArchivePassword = new Config($settingsWithoutArchivePassword);
        $this->assertEquals('', $configWithoutArchivePassword->getArchivePassword());
    }

    public function testGetArchivePath(): void
    {
        $config = new Config($this->defaultSettings);
        $this->assertEquals('/path/to/archive', $config->getArchivePath());

        // Проверяем, что при отсутствии значения возвращается пустая строка
        $settingsWithoutArchivePath = [];
        $configWithoutArchivePath = new Config($settingsWithoutArchivePath);
        $this->assertEquals('', $configWithoutArchivePath->getArchivePath());
    }

    public function testGetSiteDocumentRoot(): void
    {
        $config = new Config($this->defaultSettings);
        $this->assertEquals('/var/www/html', $config->getSiteDocumentRoot());

        // Проверяем, что при отсутствии значения возвращается пустая строка
        $settingsWithoutDocumentRoot = [];
        $configWithoutDocumentRoot = new Config($settingsWithoutDocumentRoot);
        $this->assertEquals('', $configWithoutDocumentRoot->getSiteDocumentRoot());
    }

    public function testGetCode(): void
    {
        $config = new Config($this->defaultSettings);
        $this->assertEquals('bitrixRestore', $config::getCode());
    }
}