<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Core;

use PHPUnit\Framework\TestCase;

$index = 0;
function time(): int
{
    global $index;

    return mktime(1, 30, ++$index);
}

/**
 * @internal
 *
 * @coversDefaultClass \Vasoft\BxBackupTools\Core\MessageContainer
 */
final class MessageContainerTest extends TestCase
{
    public static function provideGetStringArrayCases(): iterable
    {
        $messages = [
            'module1' => 'message1_1',
            'module2' => ['message2_1', 'message2_2'],
        ];

        return [
            [
                '',
                $messages,
                [
                    'message1_1',
                    'message2_1',
                    'message2_2',
                ],
            ],
            [
                'H:i:s',
                $messages,
                [
                    '01:30:01 message1_1',
                    '01:30:02 message2_1',
                    'message2_2',
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideGetStringArrayCases
     */
    public function testGetStringArray(string $template, array $messages, array $expected): void
    {
        global $index;
        $index = 0;
        $container = new MessageContainer($template);
        foreach ($messages as $module => $message) {
            $container->add($module, $message);
        }
        self::assertSame($expected, $container->getStringArray());
    }
}
