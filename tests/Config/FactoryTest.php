<?php

namespace Vasoft\BxBackupTools\Config;

use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function testLoadDefault()
    {
        $factory = new Factory();
        $container = $factory->load(path: realpath(__DIR__ . '/../fakes/') . '/');
        /** @var \Vasoft\BxBackupTools\Backup\FTP\Config $config */
        $config = $container->get(\Vasoft\BxBackupTools\Backup\FTP\Config::class);
        self::assertSame('192.168.1.1', $config->getRemoteHost(), 'Not replace value from distribution');
        self::assertTrue($config->getMirrorDelete(), 'Not replace value from distribution');
        self::assertSame(5, $config->getMirrorParallels(), 'Replaced value from distribution');
    }
    public function testLoadOther()
    {
        $factory = new Factory();
        $container = $factory->load('dev', realpath(__DIR__ . '/../fakes/') . '/');
        /** @var \Vasoft\BxBackupTools\Backup\FTP\Config $config */
        $config = $container->get(\Vasoft\BxBackupTools\Backup\FTP\Config::class);
        self::assertSame('192.168.1.33', $config->getRemoteHost(), 'Not replace value from distribution');
        self::assertFalse($config->getMirrorDelete(), 'Not replace value from distribution');
        self::assertSame(5, $config->getMirrorParallels(), 'Replaced value from distribution');
    }
    public function testLoadNotExists()
    {
        $factory = new Factory();
        $container = $factory->load('test', realpath(__DIR__ . '/../fakes/') . '/');
        /** @var \Vasoft\BxBackupTools\Backup\FTP\Config $config */
        $config = $container->get(\Vasoft\BxBackupTools\Backup\FTP\Config::class);
        self::assertSame('', $config->getRemoteHost());
        self::assertFalse($config->getMirrorDelete());
        self::assertSame(5, $config->getMirrorParallels());
    }
}
