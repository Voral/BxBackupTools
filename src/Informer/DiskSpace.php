<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Informer;

use Vasoft\BxBackupTools\Core\MessageContainer;
use Vasoft\BxBackupTools\Core\Task;

/**
 * Добавляет информацию о свободном месте на диске в контейнер, если свободное место меньше чем в заданном лимите.
 * Возможно использовать несколько информаторов. Если лимит 0, то сообщение о доступном месте будет выведено в любой случае.
 */
class DiskSpace implements Task
{
    public const MODULE_ID = 'Informer';

    /**
     * @param string      $path    Путь к директории на диске
     * @param int         $limit   Лимит в МиБ, если меньше которого будет выведено сообщение
     * @param null|string $message текст сообщения, если не указано, то будет выведено сообщение по умолчанию
     */
    public function __construct(
        private readonly string $path,
        private readonly int $limit,
        private readonly ?string $message = null,
    ) {}

    public function handle(MessageContainer $message, ?Task $next = null): void
    {
        $next?->handle($message);
        $freeSpace = disk_free_space($this->path) / 1024 / 1024;
        if (0 === $this->limit || $freeSpace < $this->limit) {
            $message->add(self::MODULE_ID, $this->getMessage($freeSpace));
        }
    }

    private function getMessage(float $freeSpace): string
    {
        $message = $this->message ?? sprintf('Free space on disk %s:', $this->path);

        return $message . sprintf(' %.1f MiB', $freeSpace);
    }
}
