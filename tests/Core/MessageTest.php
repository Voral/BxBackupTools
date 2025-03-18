<?php

declare(strict_types=1);
/**
 * @see https://phpunit-documentation-russian.readthedocs.io/ru/latest/
 * php -dzend_extension=xdebug.so -dxdebug.mode=coverage vendor/bin/phpunit --coverage-php .phpunit.coverage.php
 * php -dzend_extension=xdebug.so -dxdebug.mode=coverage vendor/bin/phpunit --coverage-text
 * @see https://habr.com/ru/companies/plesk/articles/552998/
 */

namespace Core;

use PHPUnit\Framework\TestCase;
use Vasoft\BxBackupTools\Core\MessageContainer;

/**
 * @internal
 * @coversDefaultClass \Vasoft\BxBackupTools\Core\Message
 */
final class MessageTest extends TestCase
{
    public static function dataTestAdd(): iterable
    {
        return [
            ['example1', 'Single string', ['Single string']],
            ['example2', ['Single string1', 'Single string2'], ['Single string1', 'Single string2']],
        ];
    }

    /**
     * @dataProvider dataTestAdd
     */
    public function testAddStringAndArrayTimed(string $module, array|string $messages, array $expected): void
    {
        $message = new MessageContainer();
        $message->add($module, $messages);
        $this->assertEquals($expected, $message->getStringArray());
    }

    /**
     * @dataProvider dataTestAdd
     */
    public function testAddStringAndArray(string $module, array|string $messages, array $expected): void
    {
        $message = new MessageContainer();
        $message->add($module, $messages);
        $this->assertEquals($expected, $message->getStringArray());
    }

    public function testAddDifferentModules(): void
    {
        $message = new MessageContainer();
        $message->add('module1', ['String 1 1', 'String 1 2']);
        $message->add('module2', ['String 2 1', 'String 2 2']);
        $message->add('module1', 'String 2 1');
        $message->add('module1', 'String 1 1');
        $this->assertEquals([
            'String 1 1',
            'String 1 2',
            'String 2 1',
            'String 2 2',
            'String 2 1',
            'String 1 1',
        ], $message->getStringArray());
    }
}
