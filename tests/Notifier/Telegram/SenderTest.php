<?php

namespace Vasoft\BxBackupTools\Notifier\Telegram {
    function stream_context_create(?array $options = null, ?array $params = null)
    {
        return [
            'stream_options' => $options,
            'params' => $params
        ];
    }

    function file_get_contents($param1, $param2, $param3)
    {
        SenderTest::$sentData = [
            'url' => $param1,
            'additional' => $param2,
            'context' => $param3
        ];
    }

    use PHPUnit\Framework\TestCase;
    use Vasoft\BxBackupTools\Core\MessageContainer;
    use Vasoft\BxBackupTools\Core\Task;

    class SenderTest extends TestCase
    {
        public static ?array $sentData = null;

        public function testHandle()
        {
            self::$sentData = null;
            $test = new Sender(new Config([
                'token' => 'tokenTest',
                'chatId' => '12123',
            ]));
            $test->handle(new MessageContainer(), new TelegramTestHandler());
            self::assertSame(
                [
                    'url' => 'https://api.telegram.org/bottokenTest/sendMessage',
                    'additional' => false,
                    'context' => [
                        'stream_options' => [
                            'http' =>
                                [
                                    'method' => 'POST',
                                    'header' => 'Content-type: application/json',
                                    'content' => '{"chat_id":"12123","text":"executed\r\ntwo lines"}',
                                ]

                        ],

                        'params' => null
                    ]
                ],
                SenderTest::$sentData
            );
        }
    }

    class ComposerFileService
    {
        public function fetchComposerJsonObject(string $composerJsonFilePath): ?object
        {
            return json_decode(file_get_contents($composerJsonFilePath, 1, 2));
        }
    }

    class TelegramTestHandler implements \Vasoft\BxBackupTools\Core\Task
    {
        public function handle(MessageContainer $message, ?Task $next = null): void
        {
            $message->add("handler", ["executed", "two lines"]);
        }
    }
}
