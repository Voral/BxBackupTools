<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Informer;

use PHPUnit\Framework\TestCase;
use Vasoft\BxBackupTools\Core\MessageContainer;
use Vasoft\BxBackupTools\Enums\DataSizeUnit;

include_once __DIR__ . '/MockTrait.php';

/**
 * @coversDefaultClass \Vasoft\BxBackupTools\Informer\DiskSpace
 *
 * @internal
 */
final class DiskSpaceTest extends TestCase
{
    use MockTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initMocks();
    }

    public function testShowInformationInMiB(): void
    {
        $path = '/path/to/dir';
        $this->clearMockDiskFreeSpace([
            $path => 4699904,
        ]);
        $container = new MessageContainer();
        $task = new DiskSpace($path, 5);
        $task->handle($container);
        $messages = $container->getStringArray();
        self::assertSame(1, self::$mockDiskFreeSpaceCount, 'Mast disk_free_space() called once');
        self::assertCount(1, $messages, 'Must be one message');
        self::assertSame('Free space on disk ' . $path . ': 4.5 MiB', $messages[0]);
    }

    public function testShowInformationInGiB(): void
    {
        $path = '/path/to/dir';
        $this->clearMockDiskFreeSpace([
            $path => 4699904000,
        ]);
        $container = new MessageContainer();
        $task = new DiskSpace($path, 5000, unit: DataSizeUnit::GiB);
        $task->handle($container);
        $messages = $container->getStringArray();
        self::assertSame('Free space on disk ' . $path . ': 4.4 GiB', $messages[0]);
    }

    public function testShowInformationInTiB(): void
    {
        $path = '/path/to/dir';
        $this->clearMockDiskFreeSpace([
            $path => 4699904000000,
        ]);
        $container = new MessageContainer();
        $task = new DiskSpace($path, 5000, unit: DataSizeUnit::TiB);
        $task->handle($container);
        $messages = $container->getStringArray();
        self::assertSame('Free space on disk ' . $path . ': 4.3 TiB', $messages[0]);
    }

    public function testShowInformationInKiB(): void
    {
        $path = '/path/to/dir';
        $this->clearMockDiskFreeSpace([
            $path => 4699904,
        ]);
        $container = new MessageContainer();
        $task = new DiskSpace($path, 5000, unit: DataSizeUnit::KiB);
        $task->handle($container);
        $messages = $container->getStringArray();
        self::assertSame('Free space on disk ' . $path . ': 4589.8 KiB', $messages[0]);
    }

    public function testShowInformationInB(): void
    {
        $path = '/path/to/dir';
        $this->clearMockDiskFreeSpace([
            $path => 4699904,
        ]);
        $container = new MessageContainer();
        $task = new DiskSpace($path, 5000000, unit: DataSizeUnit::B);
        $task->handle($container);
        $messages = $container->getStringArray();
        self::assertSame('Free space on disk ' . $path . ': 4699904.0 B', $messages[0]);
    }

    public function testCustomMessage(): void
    {
        $path = '/path/to/dir';
        $this->clearMockDiskFreeSpace([
            $path => 4699904,
        ]);
        $container = new MessageContainer();
        $task = new DiskSpace($path, 5, 'Main disk:');
        $task->handle($container);
        $messages = $container->getStringArray();
        self::assertSame(1, self::$mockDiskFreeSpaceCount, 'Mast disk_free_space() called once');
        self::assertCount(1, $messages, 'Must be one message');
        self::assertSame('Main disk: 4.5 MiB', $messages[0]);
    }

    public function testShowInformationInMiBGreaterThanLimit(): void
    {
        $path = '/path/to/dir';
        $this->clearMockDiskFreeSpace([
            $path => 4699904,
        ]);
        $container = new MessageContainer();
        $task = new DiskSpace($path, 3);
        $task->handle($container);
        $messages = $container->getStringArray();
        self::assertSame(1, self::$mockDiskFreeSpaceCount, 'Mast disk_free_space() called once');
        self::assertCount(0, $messages, 'Must be empty');
    }

    public function testShowInformationInMiBGreaterSomeTimes(): void
    {
        $data = [
            '/path/to/dir1' => 4699904,
            '/path/to/dir2' => 5699904,
            '/path/to/dir3' => 8699904,
        ];
        $this->clearMockDiskFreeSpace($data);
        $paths = array_keys($data);
        $values = array_map(static fn($val) => $val / 1024 / 1024, array_values($data));

        $container = new MessageContainer();
        $task1 = new DiskSpace($paths[0], 6);
        $task2 = new DiskSpace($paths[1], 7);
        $task3 = new DiskSpace($paths[2], 5);

        $task1->handle($container, $task2);
        $task3->handle($container);

        $messages = $container->getStringArray();
        self::assertSame(3, self::$mockDiskFreeSpaceCount, 'Mast disk_free_space() called 3 times');
        self::assertCount(2, $messages, 'Must be 2 messages');
        self::assertSame('Free space on disk ' . $paths[1] . ': 5.4 MiB', $messages[0]);
        self::assertSame('Free space on disk ' . $paths[0] . ': 4.5 MiB', $messages[1]);
    }

    public function testShowInformationNoLimit(): void
    {
        $data = [
            '/path/to/dir1' => 1,
            '/path/to/dir2' => 5699904321,
        ];
        $this->clearMockDiskFreeSpace($data);
        $paths = array_keys($data);
        $values = array_map(static fn($val) => $val / 1024 / 1024, array_values($data));

        $container = new MessageContainer();
        $task1 = new DiskSpace($paths[0], 0);
        $task2 = new DiskSpace($paths[1], 0);

        $task1->handle($container, $task2);

        $messages = $container->getStringArray();
        self::assertSame(2, self::$mockDiskFreeSpaceCount, 'Mast disk_free_space() called 2 times');
        self::assertCount(2, $messages, 'Must be 2 messages');
        self::assertSame('Free space on disk ' . $paths[1] . ': 5435.9 MiB', $messages[0]);
        self::assertSame('Free space on disk ' . $paths[0] . ': 0.0 MiB', $messages[1]);
    }
}
