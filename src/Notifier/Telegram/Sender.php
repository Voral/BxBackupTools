<?php

namespace Vasoft\BxBackupTools\Notifier\Telegram;

use Vasoft\BxBackupTools\Core\Task;
use Vasoft\BxBackupTools\Core\MessageContainer;

class Sender implements Task
{
    public function handle(MessageContainer $message, ?Task $next = null): void
    {
        $next->handle($message);
        $this->push($this->render($message->getStringArray()));
    }

    public const URL_TEMPLATE = 'https://api.telegram.org/bot%s/';
    public const URL_SEND_MESSAGE = 'sendMessage';

    public function __construct(private readonly Config $config)
    {
    }

    private function push(string $message): void
    {
        $url = sprintf(self::URL_TEMPLATE, $this->config->getToken()) . self::URL_SEND_MESSAGE;
        $data = [
            'chat_id' => $this->config->getChatId(),
            'text' => $message
        ];
        /** @todo анализ ответа */
        file_get_contents($url, false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/json',
                'content' => json_encode($data)
            ]]));
    }

    private function render($messageStrings): string
    {
        $message = implode("\r\n", $messageStrings);
        $message = preg_replace('#<br\s*/?>#i', "\r\n", $message);
        return strip_tags($message);
    }

}