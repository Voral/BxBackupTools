<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Restore;

use Vasoft\BxBackupTools\Core\Exceptions\ProcessException;

final class RestoreException extends ProcessException
{
    public function __construct(string $message, array|string $data = '')
    {
        parent::__construct($message, $data, 'restore');
    }
}
