<?php

namespace Vasoft\BxBackupTools\Core;

class MessageContainer
{
    /**
     * @var Message[] $messages
     */
    private array $messages = [];

    public function __construct(
        private readonly string $timeFormat = ''
    )
    {
    }

    public function add(string $module, string|array $data): void
    {
        $this->messages[] = new Message($module, $data);
    }

    /**
     * @return array
     */
    public function getStringArray(): array
    {
        return array_reduce($this->messages, function (array $carry, Message $item) {
            $data = is_array($item->data) ? $item->data : [$item->data];
            if ($this->timeFormat !== '') {
                $data[0] = date($this->timeFormat, $item->time) . ' ' . $data[0];
            }
            return array_merge($carry, $data);
        }, []);
    }

}