<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Backup\FTP;

use PHPUnit\Framework\TestCase;
use Vasoft\BxBackupTools\Backup\Calculator\PathCalculator;

/**
 * @internal
 *
 * @coversDefaultClass \Vasoft\BxBackupTools\Backup\FTP\Config
 */
final class ConfigTest extends TestCase
{
    /**
     * При установке вычислителей пути, для инкрементного копирования, изменять должны только
     * соответствующие значения.
     */
    public function testSetCalculators(): void
    {
        $calculatorRemote = new TestPathCalculator('remote');
        $calculatorLocal = new TestPathCalculator('local');

        $config = new Config([
            'remote' => [
                'path' => '/remote/bkp',
            ],
            'local' => [
                'path' => '/local/bkp',
            ],
        ]);
        self::assertSame('/local/bkp', $config->getLocalPath());
        $config->setLocalPathCalculator($calculatorLocal);
        self::assertSame('/remote/bkp', $config->getRemotePath());
        self::assertSame('/local/bkp_suffix_local', $config->getLocalPath());
        $config->setRemotePathCalculator($calculatorRemote);
        self::assertSame('/remote/bkp_suffix_remote', $config->getRemotePath());
        self::assertSame('/local/bkp_suffix_local', $config->getLocalPath());
    }

    public function testGetCode(): void
    {
        $config = new Config([]);
        self::assertSame('backupFTP', $config->getCode());
    }
}

class TestPathCalculator implements PathCalculator
{
    public function __construct(
        private readonly string $suffix,
    ) {}

    public function getNext(string $path): string
    {
        return $path . '_suffix_' . $this->suffix;
    }
}
