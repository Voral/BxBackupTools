<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Restore;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Vasoft\BxBackupTools\Core\MessageContainer;
use Vasoft\BxBackupTools\Core\System;

/**
 * @internal
 *
 * @coversDefaultClass \Vasoft\BxBackupTools\Restore\BitrixRestore
 */
final class BitrixRestoreTest extends TestCase
{
    public static $lastCommand = '';
    private string $archivePath;
    private string $tempDir;

    private string $bitrixDir = '';
    private string $bitrixInterfaceDir = '';
    /**
     * @var Config|(Config&MockObject)|MockObject
     */
    private null|Config|MockObject $configMock = null;
    /**
     * @var MockObject|(MockObject&System)|System
     */
    private null|\PHPUnit\Framework\MockObject\MockObject|System $systemMock = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDir = __DIR__ . '/fixtures/temp';
        $this->archivePath = __DIR__ . '/fixtures/archive';
        if (!file_exists($this->tempDir)) {
            mkdir($this->tempDir, 0o755, true);
        }

        $this->bitrixDir = $this->archivePath . '/tmp/bitrix';
        $this->bitrixInterfaceDir = $this->bitrixDir . '/php_interface';
        if (!file_exists($this->bitrixInterfaceDir)) {
            mkdir($this->bitrixInterfaceDir, 0o755, true);
        }

        file_put_contents($this->archivePath . '/backup.tar.gz', '');
        $this->initMock();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->configMock = null;
        $this->systemMock = null;

        $this->cleanArchivePath();

        rmdir($this->archivePath);
        array_map('unlink', glob($this->tempDir . '/*'));
        rmdir($this->tempDir);
    }

    public function initMock(
        string $unavailablePath = '',
        bool $isLoggingEnabled = false,
        bool $isRestoreFilesEnabled = false,
        bool $isRestoreCreditsEnabled = false,
        bool $isRestoreDatabaseEnabled = false,
        bool $isUnpackEnabled = true,
    ): void {
        self::$lastCommand = '';
        $this->configMock = $this->createMock(Config::class);
        $this->configMock->method('isUnpackEnabled')->willReturn($isUnpackEnabled);
        $this->configMock->method('isRestoreDatabaseEnabled')->willReturn($isRestoreDatabaseEnabled);
        $this->configMock->method('isRestoreCreditsEnabled')->willReturn($isRestoreCreditsEnabled);
        $this->configMock->method('isRestoreFilesEnabled')->willReturn($isRestoreFilesEnabled);
        $this->configMock->method('isLoggingEnabled')->willReturn($isLoggingEnabled);
        $this->configMock->method('getDatabaseHost')->willReturn('localhost');
        $this->configMock->method('getDatabaseName')->willReturn('test_db');
        $this->configMock->method('getDatabaseUser')->willReturn('root');
        $this->configMock->method('getDatabasePassword')->willReturn('password');
        if ('' !== $unavailablePath) {
            $this->configMock->method('getArchivePath')->willReturnCallback(function () use ($unavailablePath) {
                static $calls = 0;

                ++$calls;

                return match ($calls) {
                    2 => $unavailablePath,
                    default => $this->archivePath,
                };
            });
        } else {
            $this->configMock->method('getArchivePath')->willReturn($this->archivePath);
        }
        $this->configMock->method('getSiteDocumentRoot')->willReturn(__DIR__ . '/fixtures/site');

        $this->systemMock = $this->createMock(System::class);
        $this->systemMock->method('exec')->willReturnCallback(static function ($command, &$output, &$result) {
            $result = 0;
            $output = [];
            BitrixRestoreTest::$lastCommand = $command;

            return '';
        });
    }

    public function testHandleFullRestoreProcess(): void
    {
        $this->initMock();
        $messageContainer = new MessageContainer();
        $bitrixRestore = new BitrixRestore($this->systemMock, $this->configMock);

        try {
            $bitrixRestore->handle($messageContainer);
        } catch (\Throwable $e) {
            self::fail('An unexpected exception was thrown: ' . $e->getMessage());
        }

        $messages = $messageContainer->getStringArray();
        self::assertNotEmpty($messages, 'Messages should be logged');
        self::assertSame('Restore completed', end($messages), 'Last message should indicate successful restore');
    }

    public function testArchiveNotFound(): void
    {
        $this->cleanArchivePath();
        $this->initMock();
        $messageContainer = new MessageContainer();
        $bitrixRestore = new BitrixRestore($this->systemMock, $this->configMock);
        $this->configMock->method('getArchivePath')->willReturn('archive.tar.gz');

        $this->expectException(RestoreException::class);
        $this->expectExceptionMessage('Impossible to find archive');
        $bitrixRestore->handle($messageContainer);
    }

    /**
     * @dataProvider provideCanFindAllExtensionsCases
     */
    public function testCanFindAllExtensions(string $fileName, string $command): void
    {
        $this->cleanArchivePath();
        copy(realpath(__DIR__ . '/../fakes/') . '/' . $fileName, $this->archivePath . '/' . $fileName);
        $this->initMock();
        $messageContainer = new MessageContainer();
        $bitrixRestore = new BitrixRestore($this->systemMock, $this->configMock);
        $bitrixRestore->handle($messageContainer);
        self::assertSame(sprintf($command, $this->archivePath, $this->archivePath), self::$lastCommand);
    }

    public static function provideCanFindAllExtensionsCases(): iterable
    {
        return [
            ['archive.tar', 'cat `ls -1v %s/archive.tar*` | tar xf - -C %s/tmp 2>&1'],
            ['archive.tar.gz', 'cat `ls -1v %s/archive.tar.*` | tar xzf - -C %s/tmp 2>&1'],
            [
                'archive.enc',
                "cat `ls -1v %s/archive.enc*` | tail -c +513 | openssl aes-256-ecb -d -in - -out - -K '6434316438636439386630306232303465393830303939386563663834323765' -nosalt -nopad | tar xf - -C %s/tmp 2>&1",
            ],
            [
                'archive.enc.gz',
                "cat `ls -1v %s/archive.enc.*` | gunzip | tail -c +513 | openssl aes-256-ecb -d -in - -out - -K '6434316438636439386630306232303465393830303939386563663834323765' -nosalt -nopad | tar xf - -C %s/tmp 2>&1",
            ],
        ];
    }

    public function testExtendedLog(): void
    {
        $this->initMock(isLoggingEnabled: true, isRestoreFilesEnabled: true);
        $messageContainer = new MessageContainer();
        $bitrixRestore = new BitrixRestore($this->systemMock, $this->configMock);
        $bitrixRestore->handle($messageContainer);
        $messages = $messageContainer->getStringArray();
        self::assertMatchesRegularExpression(
            '/^\[\d{2}:\d{2}:\d{2}\] Temporary directory cleaned$/',
            $messages[0],
        );
        self::assertMatchesRegularExpression(
            '/^\[\d{2}:\d{2}:\d{2}\] Unpacked backup$/',
            $messages[1],
        );
        self::assertMatchesRegularExpression(
            '/^\[\d{2}:\d{2}:\d{2}\] Filesystem synchronization completed$/',
            $messages[2],
        );
        self::assertSame('Restore completed', $messages[3]);
    }

    public function testRestoreDatabase(): void
    {
        $dbRestoreMock = $this->createMock(DatabaseRestore::class);
        $dbRestoreMock->method('restore')->willReturnCallback(
            static function (string $fileName) use (&$fileNameProperty): void {
                $fileNameProperty = $fileName;
            },
        );
        $this->initMock(isLoggingEnabled: true, isRestoreDatabaseEnabled: true);
        $messageContainer = new MessageContainer();
        $bitrixRestore = new BitrixRestore($this->systemMock, $this->configMock, $dbRestoreMock);
        $bitrixRestore->handle($messageContainer);
        $expected = $this->configMock->getArchivePath() . '/tmp/bitrix/backup/backup.sql';
        self::assertSame($expected, $fileNameProperty, 'Wrong sql script filename.');

        $messages = $messageContainer->getStringArray();
        self::assertMatchesRegularExpression('/^\[\d{2}:\d{2}:\d{2}\] Restored database backup$/', $messages[2]);
    }

    public function testRestoreCreditsDbConnNotFound(): void
    {
        $this->initMock(isLoggingEnabled: true, isRestoreCreditsEnabled: true);
        $messageContainer = new MessageContainer();
        $bitrixRestore = new BitrixRestore($this->systemMock, $this->configMock);
        $this->expectException(RestoreException::class);
        $this->expectExceptionMessage('File bitrix/php_interface/dbconn.php not found');
        $bitrixRestore->handle($messageContainer);
    }

    public function testRestoreCreditsSettingsNotFound(): void
    {
        $this->initMock(isLoggingEnabled: true, isRestoreCreditsEnabled: true);

        if (!file_exists($this->bitrixInterfaceDir)) {
            mkdir($this->bitrixInterfaceDir, 0o755, true);
        }
        file_put_contents($this->bitrixInterfaceDir . '/dbconn.php', 'test');
        $messageContainer = new MessageContainer();
        $bitrixRestore = new BitrixRestore($this->systemMock, $this->configMock);
        $this->expectException(RestoreException::class);
        $this->expectExceptionMessage('File bitrix/.settings.php not found');
        $bitrixRestore->handle($messageContainer);
    }

    public function testRestoreCreditsSettings(): void
    {
        $this->initMock(isLoggingEnabled: true, isRestoreCreditsEnabled: true);

        if (!file_exists($this->bitrixInterfaceDir)) {
            mkdir($this->bitrixInterfaceDir, 0o755, true);
        }
        file_put_contents(
            $this->bitrixInterfaceDir . '/dbconn.php',
            '<' . "?\n\$DBHost='***';\n\$DBName='db***';\n\$DBLogin='lg***';\n\$DBPassword='pa***';",
        );

        file_put_contents(
            $this->bitrixDir . '/.settings.php',
            '<' . "?php\nreturn " . var_export([
                'connections' => [
                    'value' => [
                        'default' => [
                            'host' => 'host*****',
                            'login' => 'login*****',
                            'password' => 'pa*****',
                            'database' => 'db*****',
                        ],
                    ],
                ],
            ], true) . ';',
        );
        $messageContainer = new MessageContainer();
        $bitrixRestore = new BitrixRestore($this->systemMock, $this->configMock);
        $bitrixRestore->handle($messageContainer);
        $content = file_get_contents($this->bitrixInterfaceDir . '/dbconn.php');
        self::assertSame(
            '<' . "?\n\$DBHost = 'localhost';\n\$DBName = 'test_db';\n\$DBLogin = 'root';\n\$DBPassword = 'password';",
            $content,
            'Wrong dbconn.php',
        );
        $config = require $this->bitrixDir . '/.settings.php';

        self::assertSame(
            'localhost',
            $config['connections']['value']['default']['host'],
            'Wrong .settings.php host',
        );
        self::assertSame('root', $config['connections']['value']['default']['login'], 'Wrong .settings.php login');
        self::assertSame(
            'password',
            $config['connections']['value']['default']['password'],
            'Wrong .settings.php password',
        );
        self::assertSame(
            'test_db',
            $config['connections']['value']['default']['database'],
            'Wrong .settings.php database',
        );
        unlink($this->bitrixInterfaceDir . '/dbconn.php');
        unlink($this->bitrixDir . '/.settings.php');
    }

    public function testMustHandleExecErrorsFromOutput(): void
    {
        $this->initMock();
        $this->systemMock->method('exec')->willReturnCallback(static function ($command, &$output, &$result) {
            $result = 1;
            $output = ['Some error message'];
            BitrixRestoreTest::$lastCommand = $command;

            return '';
        });

        $messageContainer = new MessageContainer();
        $bitrixRestore = new BitrixRestore($this->systemMock, $this->configMock);
        $this->expectException(RestoreException::class);
        $this->expectExceptionMessage('Some error message');

        $bitrixRestore->handle($messageContainer);
    }

    public function testMustHandleExecErrorsFromErrors(): void
    {
        $this->initMock();
        $this->systemMock->method('exec')->willReturnCallback(static function ($command, &$output, &$result) {
            $result = 1;
            $output = '';
            BitrixRestoreTest::$lastCommand = $command;

            return '';
        });

        $messageContainer = new MessageContainer();
        $bitrixRestore = new BitrixRestore($this->systemMock, $this->configMock);
        $this->expectException(RestoreException::class);
        $this->expectExceptionMessage('Impossible to clean temporary directory');

        $bitrixRestore->handle($messageContainer);
    }

    public function testMustThrowExceptionOnCreateDirectoryMistake(): void
    {
        $tempDir = sys_get_temp_dir() . \DIRECTORY_SEPARATOR . uniqid('test_', true);
        mkdir($tempDir);

        try {
            chmod($tempDir, 0o555);
            $this->initMock(unavailablePath: $tempDir);
            $messageContainer = new MessageContainer();
            $bitrixRestore = new BitrixRestore($this->systemMock, $this->configMock);
            $this->expectException(RestoreException::class);
            $this->expectExceptionMessage("Impossible to create directory {$tempDir}/tmp");
            $bitrixRestore->handle($messageContainer);
        } finally {
            chmod($tempDir, 0o777);
            rmdir($tempDir);
        }
    }

    protected function cleanArchivePath(): void
    {
        if (file_exists($this->bitrixInterfaceDir)) {
            array_map('unlink', glob($this->bitrixInterfaceDir . '/*'));
            rmdir($this->bitrixInterfaceDir);
        }
        if (file_exists($this->bitrixDir)) {
            array_map('unlink', glob($this->bitrixDir . '/*'));
            rmdir($this->bitrixDir);
        }

        array_map('unlink', glob($this->archivePath . '/tmp/*'));
        if (file_exists($this->archivePath . '/tmp')) {
            rmdir($this->archivePath . '/tmp');
        }
        array_map('unlink', glob($this->archivePath . '/*'));
    }
}
