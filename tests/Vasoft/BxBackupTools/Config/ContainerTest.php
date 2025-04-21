<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Config;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass \Vasoft\BxBackupTools\Config\Container
 */
final class ContainerTest extends TestCase
{
    /**
     * Контейнер должен принимать настройки для всех конфигураций и геттер возвращать объект конфига
     * соответсвующий запрошенному.
     */
    public function testGet(): void
    {
        $container = $this->getContainer();
        /**
         * @var TestContainerConfig1 $config1
         */
        $config1 = $container->get(TestContainerConfig1::class);
        /**
         * @var TestContainerConfig2 $config2
         */
        $config2 = $container->get(TestContainerConfig2::class);

        self::assertSame(TestContainerConfig1::class, $config1::class, 'Wrong first config class');
        self::assertSame('value1', $config1->getValue(), 'Wrong first config value');
        self::assertSame(TestContainerConfig2::class, $config2::class, 'Wrong second config class');
        self::assertSame('value2', $config2->getValue(), 'Wrong second config value');
    }

    /**
     * Если настроек для запрошенного конфига нет в контейнере, то выбрасывается исключение.
     */
    public function testGetUnknown(): void
    {
        self::expectExceptionMessage('Config with code example3 not found');
        self::expectException(\InvalidArgumentException::class);
        $this->getContainer()->get(TestContainerConfig3::class);
    }

    /**
     * Конструктор для каждого должен вызываться один раз.
     */
    public function testGetOnce(): void
    {
        $container = $this->getContainer();

        $config1 = $container->get(TestContainerConfig1::class);
        $expectedId = spl_object_id($config1);
        /**
         * @var TestContainerConfig1 $config1
         */
        $config1a = $container->get(TestContainerConfig1::class);
        self::assertSame($expectedId, spl_object_id($config1a), 'Multiple configs entity id should be the same');
    }

    private function getContainer(): Container
    {
        static $container = null;
        if (null === $container) {
            $container = new Container([
                TestContainerConfig1::CODE => ['key1' => 'value1'],
                TestContainerConfig2::CODE => ['key2' => 'value2'],
            ]);
        }

        return $container;
    }
}

class TestContainerConfig1 extends Config
{
    public const CODE = 'example1';

    public static function getCode(): string
    {
        return self::CODE;
    }

    public function getValue(): string
    {
        return $this->settings['key1'] ?: 'no value';
    }
}

class TestContainerConfig2 extends Config
{
    public const CODE = 'example2';

    public static function getCode(): string
    {
        return self::CODE;
    }

    public function getValue(): string
    {
        return $this->settings['key2'] ?: 'no value';
    }
}

class TestContainerConfig3 extends Config
{
    public const CODE = 'example3';

    public static function getCode(): string
    {
        return self::CODE;
    }
}
