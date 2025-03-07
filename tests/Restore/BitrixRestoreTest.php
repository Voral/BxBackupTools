<?php

namespace Vasoft\BxBackupTools\Restore;

use PHPUnit\Framework\TestCase;
use Vasoft\BxBackupTools\Core\MessageContainer;
use Vasoft\BxBackupTools\Core\System;
use Vasoft\BxBackupTools\Core\Task;

class BitrixRestoreTest extends TestCase
{
    private string $archivePath;
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        // Создаем временные директории и файлы для тестов
        $this->archivePath = __DIR__ . '/fixtures/archive';
        if (!file_exists($this->archivePath)) {
            mkdir($this->archivePath, 0755, true);
        }

        $this->tempDir = __DIR__ . '/fixtures/temp';
        if (!file_exists($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }

        // Создаем тестовый архив
        file_put_contents($this->archivePath . '/backup.tar.gz', '');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

//        // Очищаем временные директории после тестов
//        array_map('unlink', glob($this->archivePath . '/*'));
//        rmdir($this->archivePath);
//
//        array_map('unlink', glob($this->tempDir . '/*'));
//        rmdir($this->tempDir);
    }

    public function testHandleFullRestoreProcess(): void
    {
        $configMock = $this->createMock(Config::class);
        $configMock->method('isUnpackEnabled')->willReturn(true);
        $configMock->method('isRestoreDatabaseEnabled')->willReturn(false);
        $configMock->method('isRestoreCreditsEnabled')->willReturn(false);
        $configMock->method('isRestoreFilesEnabled')->willReturn(false);
        $configMock->method('isLoggingEnabled')->willReturn(true);
        $configMock->method('getDatabaseHost')->willReturn('localhost');
        $configMock->method('getDatabaseName')->willReturn('test_db');
        $configMock->method('getDatabaseUser')->willReturn('root');
        $configMock->method('getDatabasePassword')->willReturn('password');
        $configMock->method('getArchivePath')->willReturn($this->archivePath);
        $configMock->method('getSiteDocumentRoot')->willReturn(__DIR__ . '/fixtures/site');

        $systemMock = $this->createMock(System::class);
        $systemMock->method('exec')->willReturnCallback(function ($command, &$output, &$result) {
            $result = 0;
            $output = [];
            return '';
        });

        $messageContainer = new MessageContainer();
        $bitrixRestore = new BitrixRestore($systemMock, $configMock);
        try {
            $bitrixRestore->handle($messageContainer);
        } catch (\Throwable $e) {
            $this->fail("An unexpected exception was thrown: " . $e->getMessage());
        }

        // Проверяем логи
        $messages = $messageContainer->getStringArray();
        $this->assertNotEmpty($messages, 'Messages should be logged');
        $this->assertEquals('Restore completed', end($messages), 'Last message should indicate successful restore');
    }
}