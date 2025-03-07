<?php

namespace Vasoft\BxBackupTools\Backup\FTP;

use PHPUnit\Framework\TestCase;
use Vasoft\BxBackupTools\Core\MessageContainer;
use Vasoft\BxBackupTools\Core\SystemCmd;

class UploaderTest extends TestCase
{
    /**
     * @param array $modifier
     * @param string $expected
     * @param string $message
     * @return void
     * @throws Exception
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @dataProvider dataConfigRelations
     */
    public function testConfigRelations(array $modifier, string $expected, string $message): void
    {
        $settings = array_replace_recursive($this->getDefaultConfig(), $modifier);
        $config = new Config($settings);
        $commandValue = '';
        $cmd = $this->createStub(SystemCmd::class);
        $cmd->method('exec')
            ->willReturnCallback(function (string $command, &$output, &$resultCode) use (&$commandValue) {
                $output = '';
                $resultCode = 0;
                $commandValue = $command;
                return '';
            });
        $client = new Uploader($cmd, $config);
        $messages = new MessageContainer();
        $client->handle($messages);
        $this->assertSame($expected, $commandValue, $message);

    }

    public static function dataConfigRelations(): array
    {
        return [
            [[], 'lftp -c "set ftp:ssl-allow true;set ssl:verify-certificate no;open ftp://user1:p123@2.3.4.5;mirror --parallel=5 --delete -R /v/bkp /bkp;bye" 2>&1', 'Default Config mistake'],
            [['mirror' => ['delete' => false]], 'lftp -c "set ftp:ssl-allow true;set ssl:verify-certificate no;open ftp://user1:p123@2.3.4.5;mirror --parallel=5 -R /v/bkp /bkp;bye" 2>&1', 'Mirror Not Delete'],
            [['mirror' => ['parallel' => 2]], 'lftp -c "set ftp:ssl-allow true;set ssl:verify-certificate no;open ftp://user1:p123@2.3.4.5;mirror --parallel=2 --delete -R /v/bkp /bkp;bye" 2>&1', 'Mirror Parallel'],
            [['local' => ['path' => '/home/backup']], 'lftp -c "set ftp:ssl-allow true;set ssl:verify-certificate no;open ftp://user1:p123@2.3.4.5;mirror --parallel=5 --delete -R /home/backup /bkp;bye" 2>&1', 'Local Path'],
            [['remote' => ['password' => 'PassMy']], 'lftp -c "set ftp:ssl-allow true;set ssl:verify-certificate no;open ftp://user1:PassMy@2.3.4.5;mirror --parallel=5 --delete -R /v/bkp /bkp;bye" 2>&1', 'Remote Password'],
            [['remote' => ['user' => 'user2']], 'lftp -c "set ftp:ssl-allow true;set ssl:verify-certificate no;open ftp://user2:p123@2.3.4.5;mirror --parallel=5 --delete -R /v/bkp /bkp;bye" 2>&1', 'Remote User'],
            [['remote' => ['path' => '/test']], 'lftp -c "set ftp:ssl-allow true;set ssl:verify-certificate no;open ftp://user1:p123@2.3.4.5;mirror --parallel=5 --delete -R /v/bkp /test;bye" 2>&1', 'Remote Path'],
            [['remote' => ['protocol' => 'sftp']], 'lftp -c "open sftp://user1:p123@2.3.4.5;mirror --parallel=5 --delete -R /v/bkp /bkp;bye" 2>&1', 'Remote Protocol SFTP'],
            [['remote' => ['host' => '1.1.1.1']], 'lftp -c "set ftp:ssl-allow true;set ssl:verify-certificate no;open ftp://user1:p123@1.1.1.1;mirror --parallel=5 --delete -R /v/bkp /bkp;bye" 2>&1', 'Remote Host']
        ];
    }

    /**
     * @return void
     * @throws Exception
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testDownloadSuccess(): void
    {
        $config = new Config($this->getDefaultConfig());
        $cmd = $this->createStub(SystemCmd::class);
        $cmd->method('exec')
            ->willReturn('')
            ->willReturnCallback(function (string $command, &$output, &$resultCode) {
                $output = '';
                $resultCode = 0;
                return '';
            });
        $client = new Uploader($cmd, $config);
        $messages = new MessageContainer();
        $client->handle($messages);
        $this->assertSame(['Backup upload completed successfully'], $messages->getStringArray());
    }

    /**
     * @return void
     * @throws Exception
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testDownloadError(): void
    {
        $config = new Config($this->getDefaultConfig());
        $cmd = $this->createStub(SystemCmd::class);
        $cmd->method('exec')
            ->willReturn('')
            ->willReturnCallback(function (string $command, &$output, &$resultCode) {
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
            $this->assertSame(['Test error message'], $e->data);
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
