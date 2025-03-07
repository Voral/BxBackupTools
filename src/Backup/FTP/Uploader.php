<?php

namespace Vasoft\BxBackupTools\Backup\FTP;

class Uploader extends Client
{

    protected function getMirrorParams(): array
    {
        $params = parent::getMirrorParams();
        $params[] = '-R';
        return $params;
    }

    protected function getSourcePath(): string
    {
        return $this->config->getLocalPath();
    }

    protected function getDestinationPath(): string
    {
        return $this->config->getRemotePath();
    }

    protected function getSuccessMessage(): string
    {
        return 'Backup upload completed successfully';
    }

    protected function getErrorMessage(): string
    {
        return 'Upload error:';
    }
}