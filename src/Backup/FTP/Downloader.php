<?php

namespace Vasoft\BxBackupTools\Backup\FTP;

class Downloader extends Client
{
    protected function getSourcePath(): string
    {
        return $this->config->getRemotePath();
    }

    protected function getDestinationPath(): string
    {
        return $this->config->getLocalPath();
    }

    protected function getSuccessMessage(): string
    {
        return 'Backup download completed';
    }

    protected function getErrorMessage(): string
    {
        return 'Download error:';
    }
}