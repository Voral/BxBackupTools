<?php
/**
 * @see https://phpunit-documentation-russian.readthedocs.io/ru/latest/
 * php -dzend_extension=xdebug.so -dxdebug.mode=coverage vendor/bin/phpunit --coverage-php .phpunit.coverage.php
 * php -dzend_extension=xdebug.so -dxdebug.mode=coverage vendor/bin/phpunit --coverage-text
 * @see https://habr.com/ru/companies/plesk/articles/552998/
 */

namespace Core;

use Vasoft\BxBackupTools\Core\Message;
use Vasoft\BxBackupTools\Core\MessageContainer;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    /**
     * @return void
     * @dataProvider dataTestAdd
     */
    public function testAddStringAndArrayTimed(string $module, string|array $messages, array $expected)
    {
        $message = new MessageContainer();
        $message->add($module, $messages);
        $this->assertEquals($expected, $message->getStringArray());
    }
    /**
     * @return void
     * @dataProvider dataTestAdd
     */
    public function testAddStringAndArray(string $module, string|array $messages, array $expected)
    {
        $message = new MessageContainer();
        $message->add($module, $messages);
        $this->assertEquals($expected, $message->getStringArray());
    }

    public static function dataTestAdd(): array
    {
        return [
            ['example1', 'Single string', ['Single string']],
            ['example2', ['Single string1', 'Single string2'], ['Single string1', 'Single string2']],
        ];
    }

    public function testAddDifferentModules()
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
