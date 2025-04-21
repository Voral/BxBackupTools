<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Core;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass \Vasoft\BxBackupTools\Core\Message
 */
final class MessageTest extends TestCase
{
    /**
     * @dataProvider dataTestAdd
     */
    public function testAddStringAndArrayTimed(string $module, array|string $messages, array $expected): void
    {
        $message = new MessageContainer();
        $message->add($module, $messages);
        self::assertSame($expected, $message->getStringArray());
    }

    /**
     * @dataProvider dataTestAdd
     */
    public function testAddStringAndArray(string $module, array|string $messages, array $expected): void
    {
        $message = new MessageContainer();
        $message->add($module, $messages);
        self::assertSame($expected, $message->getStringArray());
    }

    public static function dataTestAdd(): iterable
    {
        return [
            ['example1', 'Single string', ['Single string']],
            ['example2', ['Single string1', 'Single string2'], ['Single string1', 'Single string2']],
        ];
    }

    public function testAddDifferentModules(): void
    {
        $message = new MessageContainer();
        $message->add('module1', ['String 1 1', 'String 1 2']);
        $message->add('module2', ['String 2 1', 'String 2 2']);
        $message->add('module1', 'String 2 1');
        $message->add('module1', 'String 1 1');
        self::assertSame([
            'String 1 1',
            'String 1 2',
            'String 2 1',
            'String 2 2',
            'String 2 1',
            'String 1 1',
        ], $message->getStringArray());
    }
}
