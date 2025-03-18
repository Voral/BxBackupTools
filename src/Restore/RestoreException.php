<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Restore;

use Vasoft\BxBackupTools\Core\Exceptions\ProcessException;

final class RestoreException extends ProcessException
{
    /**
     * @param string $message
     * @param array<mixed>|string $data
     */
    public function __construct(string $message, array|string $data = '')
    {
        parent::__construct($message, $data, 'restore');
    }
}
