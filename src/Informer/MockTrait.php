<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Informer;

use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;

trait MockTrait
{
    use PHPMock;

    private bool $initialized = false;

    protected static int $mockDiskFreeSpaceCount = 0;
    protected static array $mockDiskFreeSpaceResults = [];
    protected static array $mockDiskFreeSpaceParams = [];

    protected function initMocks(): void
    {
        if (!$this->initialized) {
            $mockDiskFreeSpace = $this->getFunctionMock(__NAMESPACE__, 'disk_free_space');
            $mockDiskFreeSpace->expects(TestCase::any())->willReturnCallback(
                static function (string $path): false|float {
                    self::$mockDiskFreeSpaceParams[] = $path;
                    ++self::$mockDiskFreeSpaceCount;

                    return self::$mockDiskFreeSpaceResults[$path] ?? false;
                },
            );
            $this->initialized = true;
        }
    }

    protected function clearMockDiskFreeSpace(array $results): void
    {
        self::$mockDiskFreeSpaceCount = 0;
        self::$mockDiskFreeSpaceResults = $results;
        self::$mockDiskFreeSpaceParams = [];
    }
}
