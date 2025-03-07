<?php

namespace Vasoft\BxBackupTools\Core;

use Vasoft\BxBackupTools\Core\MessageContainer;
use PHPUnit\Framework\TestCase;

$index = 0;
function time(): int
{
    global $index;
    return mktime(1, 30, ++$index);
}

class MessageContainerTest extends TestCase
{
    /**
     * @param string $template
     * @param array $messages
     * @param array $expected
     * @return void
     * @dataProvider dataGetStringArray
     */
    public function testGetStringArray(string $template, array $messages, array $expected): void
    {
        global $index;
        $index = 0;
        $container = new MessageContainer($template);
        foreach ($messages as $module => $message) {
            $container->add($module, $message);
        }
        $this->assertEquals($expected, $container->getStringArray());
    }

    public static function dataGetStringArray(): array
    {
        $messages = [
            'module1' => 'message1_1',
            'module2' => ['message2_1', 'message2_2',],
        ];
        return [
            [
                '',
                $messages,
                [
                    'message1_1',
                    'message2_1',
                    'message2_2',
                ]
            ],
            [
                'H:i:s',
                $messages,
                [
                    '01:30:01 message1_1',
                    '01:30:02 message2_1',
                    'message2_2',
                ]
            ],
        ];
    }
}
