<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Core\Exceptions;

/**
 * Исключение сообщение о котором должно быть отправлено в сообщение.
 */
abstract class ProcessException extends AppException
{
    public function __construct(
        string $message,
        public readonly array|string $data,
        public readonly string $module,
    ) {
        parent::__construct($message);
    }
}
