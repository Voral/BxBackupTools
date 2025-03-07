<?php

namespace Vasoft\BxBackupTools\Config;

use JetBrains\PhpStorm\NoReturn;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    private function getContainer(): Container{
        static $container = null;
        if ($container === null) {
            $container = new Container([
                TestContainerConfig1::CODE => ['key1' => 'value1'],
                TestContainerConfig2::CODE => ['key2' => 'value2'],
            ]);
        }
        return $container;
    }

    /**
     * Контейнер должен принимать настройки для всех конфигураций и геттер возвращать объект конфига
     * соответсвующий запрошенному.
     * @return void
     *
     */
    public function testGet()
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
     * @return void
     */
    public function testGetUnknown()
    {
        self::expectExceptionMessage('Config with code example3 not found');
        self::expectException(\InvalidArgumentException::class);
        $this->getContainer()->get(TestContainerConfig3::class);
    }

    /**
     * Конструктор для каждого должен вызываться один раз.
     * @return void
     */
    public function testGetOnce()
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
}

class TestContainerConfig1 extends Config
{
    public const CODE = 'example1';

    public static function getCode(): string
    {
        return TestContainerConfig1::CODE;
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
        return TestContainerConfig2::CODE;
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
        return TestContainerConfig3::CODE;
    }
}