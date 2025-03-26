<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Backup\FTP;

use PHPUnit\Framework\TestCase;
use Vasoft\BxBackupTools\Core\MessageContainer;
use Vasoft\BxBackupTools\Core\System;

/**
 * @internal
 *
 * @coversDefaultClass \Vasoft\BxBackupTools\Backup\FTP\Uploader
 */
final class UploaderTest extends TestCase
{
    public static function provideConfigRelationsCases(): iterable
    {
        return [
            [
                [],
                'lftp -c "set ftp:ssl-allow true;set ssl:verify-certificate no;open ftp://user1:p123@2.3.4.5;mirror --parallel=5 --delete -R /v/bkp /bkp;bye" 2>&1',
                'Default Config mistake',
            ],
            [
                ['mirror' => ['delete' => false]],
                'lftp -c "set ftp:ssl-allow true;set ssl:verify-certificate no;open ftp://user1:p123@2.3.4.5;mirror --parallel=5 -R /v/bkp /bkp;bye" 2>&1',
                'Mirror Not Delete',
            ],
            [
                ['mirror' => ['parallel' => 2]],
                'lftp -c "set ftp:ssl-allow true;set ssl:verify-certificate no;open ftp://user1:p123@2.3.4.5;mirror --parallel=2 --delete -R /v/bkp /bkp;bye" 2>&1',
                'Mirror Parallel',
            ],
            [
                ['local' => ['path' => '/home/backup']],
                'lftp -c "set ftp:ssl-allow true;set ssl:verify-certificate no;open ftp://user1:p123@2.3.4.5;mirror --parallel=5 --delete -R /home/backup /bkp;bye" 2>&1',
                'Local Path',
            ],
            [
                ['remote' => ['password' => 'PassMy']],
                'lftp -c "set ftp:ssl-allow true;set ssl:verify-certificate no;open ftp://user1:PassMy@2.3.4.5;mirror --parallel=5 --delete -R /v/bkp /bkp;bye" 2>&1',
                'Remote Password',
            ],
            [
                ['remote' => ['user' => 'user2']],
                'lftp -c "set ftp:ssl-allow true;set ssl:verify-certificate no;open ftp://user2:p123@2.3.4.5;mirror --parallel=5 --delete -R /v/bkp /bkp;bye" 2>&1',
                'Remote User',
            ],
            [
                ['remote' => ['path' => '/test']],
                'lftp -c "set ftp:ssl-allow true;set ssl:verify-certificate no;open ftp://user1:p123@2.3.4.5;mirror --parallel=5 --delete -R /v/bkp /test;bye" 2>&1',
                'Remote Path',
            ],
            [
                ['remote' => ['protocol' => 'sftp']],
                'lftp -c "open sftp://user1:p123@2.3.4.5;mirror --parallel=5 --delete -R /v/bkp /bkp;bye" 2>&1',
                'Remote Protocol SFTP',
            ],
            [
                ['remote' => ['host' => '1.1.1.1']],
                'lftp -c "set ftp:ssl-allow true;set ssl:verify-certificate no;open ftp://user1:p123@1.1.1.1;mirror --parallel=5 --delete -R /v/bkp /bkp;bye" 2>&1',
                'Remote Host',
            ],
        ];
    }

    /**
     * @throws Exception
     * @throws \PHPUnit\Framework\MockObject\Exception
     *
     * @dataProvider provideConfigRelationsCases
     */
    public function testConfigRelations(array $modifier, string $expected, string $message): void
    {
        $settings = array_replace_recursive($this->getDefaultConfig(), $modifier);
        $config = new Config($settings);
        $commandValue = '';
        $cmd = self::createStub(System::class);
        $cmd->method('exec')
            ->willReturnCallback(static function (string $command, &$output, &$resultCode) use (&$commandValue) {
                $output = '';
                $resultCode = 0;
                $commandValue = $command;

                return '';
            });
        $client = new Uploader($cmd, $config);
        $messages = new MessageContainer();
        $client->handle($messages);
        self::assertSame($expected, $commandValue, $message);
    }

    /**
     * @throws Exception
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testDownloadSuccess(): void
    {
        $config = new Config($this->getDefaultConfig());
        $cmd = self::createStub(System::class);
        $cmd->method('exec')
            ->willReturn('')
            ->willReturnCallback(static function (string $command, &$output, &$resultCode) {
                $output = '';
                $resultCode = 0;

                return '';
            });
        $client = new Uploader($cmd, $config);
        $messages = new MessageContainer();
        $client->handle($messages);
        self::assertSame(['Backup upload completed successfully'], $messages->getStringArray());
    }

    /**
     * @throws Exception
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testDownloadError(): void
    {
        $config = new Config($this->getDefaultConfig());
        $cmd = self::createStub(System::class);
        $cmd->method('exec')
            ->willReturn('')
            ->willReturnCallback(static function (string $command, &$output, &$resultCode) {
                $output = ['Test error message'];
                $resultCode = 1;

                return '';
            });
        $client = new Uploader($cmd, $config);
        $messages = new MessageContainer();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Upload error:');

        try {
            $client->handle($messages);
        } catch (Exception $e) {
            self::assertSame(['Test error message'], $e->data);

            throw $e;
        }
    }

    private function getDefaultConfig(): array
    {
        return [
            'remote' => [
                'host' => '2.3.4.5',
                'protocol' => 'ftp',
                'path' => '/bkp',
                'user' => 'user1',
                'password' => 'p123',
            ],
            'local' => [
                'path' => '/v/bkp',
            ],
            'mirror' => [
                'parallel' => 5,
                'delete' => true,
            ],
        ];
    }
}
