<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Informer;

use Vasoft\BxBackupTools\Core\MessageContainer;
use Vasoft\BxBackupTools\Core\Task;
use Vasoft\BxBackupTools\Enums\DataSizeUnit;

/**
 * Добавляет информацию о свободном месте на диске в контейнер, если свободное место меньше чем в заданном лимите.
 * Возможно использовать несколько информаторов. Если лимит 0, то сообщение о доступном месте будет выведено в любой случае.
 */
class DiskSpace implements Task
{
    public const MODULE_ID = 'Informer';

    /**
     * @param string       $path    Путь к директории на диске
     * @param int          $limit   Лимит в заданных единицах измерения (по умолчанию МиБ), если меньше которого будет выведено сообщение
     * @param null|string  $message текст сообщения, если не указано, то будет выведено сообщение по умолчанию
     * @param DataSizeUnit $unit    Единица измерения, по умолчанию МиБ (DataSizeUnit::MiB)
     */
    public function __construct(
        private readonly string $path,
        private readonly int $limit,
        private readonly ?string $message = null,
        private readonly DataSizeUnit $unit = DataSizeUnit::MiB,
    ) {}

    public function handle(MessageContainer $message, ?Task $next = null): void
    {
        $next?->handle($message);
        $freeSpace = $this->unit->convert(disk_free_space($this->path));
        if (0 === $this->limit || $freeSpace < $this->limit) {
            $message->add(self::MODULE_ID, $this->getMessage($freeSpace));
        }
    }

    private function getMessage(float $freeSpace): string
    {
        $message = $this->message ?? sprintf('Free space on disk %s:', $this->path);

        return $message . sprintf(' %.1f ', $freeSpace) . $this->unit->value;
    }
}
