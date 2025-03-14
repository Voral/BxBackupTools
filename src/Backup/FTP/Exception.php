<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Backup\FTP;

use Vasoft\BxBackupTools\Core\Exceptions\ProcessException;

final class Exception extends ProcessException
{
    public function __construct(string $message, array|string $messages)
    {
        parent::__construct($message, $messages, Client::MODULE_ID);
    }
}
