<?php

namespace Vasoft\BxBackupTools\Restore;

class RestoreException extends \Vasoft\BxBackupTools\Core\Exceptions\ProcessException
{
    public function __construct(string $message, array|string $data = '')
    {
        parent::__construct($message, $data, 'restore');
    }
}