<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Config;

use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;
use Vasoft\BxBackupTools\Backup\FTP\Config;

/**
 * @internal
 *
 * @coversDefaultClass \Vasoft\BxBackupTools\Config\Factory
 */
final class FactoryTest extends TestCase
{
    use PHPMock;

    public function testLoadDefault(): void
    {
        $factory = new Factory();
        $container = $factory->load(path: realpath(__DIR__ . '/../fakes/') . '/');
        /** @var Config $config */
        $config = $container->get(Config::class);
        self::assertSame('192.168.1.1', $config->getRemoteHost(), 'Not replace value from distribution');
        self::assertTrue($config->getMirrorDelete(), 'Not replace value from distribution');
        self::assertSame(5, $config->getMirrorParallels(), 'Replaced value from distribution');
    }

    public function testLoadOther(): void
    {
        $factory = new Factory();
        $container = $factory->load('dev', realpath(__DIR__ . '/../fakes/') . '/');
        /** @var Config $config */
        $config = $container->get(Config::class);
        self::assertSame('192.168.1.33', $config->getRemoteHost(), 'Not replace value from distribution');
        self::assertFalse($config->getMirrorDelete(), 'Not replace value from distribution');
        self::assertSame(5, $config->getMirrorParallels(), 'Replaced value from distribution');
    }

    public function testLoadNotExists(): void
    {
        $factory = new Factory();
        $container = $factory->load('test', realpath(__DIR__ . '/../fakes/') . '/');
        /** @var Config $config */
        $config = $container->get(Config::class);
        self::assertSame('', $config->getRemoteHost());
        self::assertFalse($config->getMirrorDelete());
        self::assertSame(5, $config->getMirrorParallels());
    }

    public function testGetDefaultPath(): void
    {
        $factory = $this->getMockBuilder(Factory::class)
            ->onlyMethods(['getRoot'])
            ->getMock();
        $factory->expects(self::once())
            ->method('getRoot')
            ->willReturn('/mocked/path/');

        $factory->load();
    }

    public function testGetPathFromProps(): void
    {
        $factory = $this->getMockBuilder(Factory::class)
            ->onlyMethods(['getRoot'])
            ->getMock();
        $factory->expects(self::never())
            ->method('getRoot');
        $factory->load(path: '/expected/path/');
    }

    public function testGetRootReturnsCorrectPath(): void
    {
        $getIncludedFiles = $this->getFunctionMock(__NAMESPACE__, 'get_included_files');
        $getIncludedFiles->expects(self::once())
            ->willReturn(['/path/to/file.php']);
        $factory = new Factory();
        $root = $factory->getRoot();
        self::assertSame('/path/to/', $root);
    }
}
