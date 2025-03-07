<?php

namespace Vasoft\BxBackupTools\Backup\FTP;

use Vasoft\BxBackupTools\Core\MessageContainer;
use Vasoft\BxBackupTools\Core\System;
use Vasoft\BxBackupTools\Core\Task;

abstract class Client implements Task
{
    public const MODULE_ID = 'backup';

    public function __construct(
        protected readonly System $cmd,
        protected readonly Config $config
    )
    {
    }

    /**
     * @param MessageContainer $message
     * @param Task|null $next
     * @return void
     * @throws Exception
     */
    public function handle(MessageContainer $message, ?Task $next = null): void
    {
        $commands = [];
        $this->fillSSLConfigCommand($commands);
        $commands[] = $this->getOpenCommand();
        $commands[] = $this->getMirroringCommand();
        $commands[] = 'bye';
        $command = sprintf('lftp -c "%s" 2>&1', implode(';', $commands));
        $output = $return = null;
        $this->cmd->exec($command, $output, $return);
        if ($return !== 0) {
            throw new Exception($this->getErrorMessage(), $output);
        } else {
            $message->add(self::MODULE_ID, $this->getSuccessMessage());
        }
    }

    abstract protected function getSuccessMessage(): string;

    abstract protected function getErrorMessage(): string;

    protected function fillSSLConfigCommand(array &$commands): void
    {
        if (strtoupper($this->config->getRemoteProtocol()) === 'FTP') {
            $commands[] = 'set ftp:ssl-allow true';
            $commands[] = 'set ssl:verify-certificate no';
        }
    }

    protected function getMirroringCommand(): string
    {
        return sprintf(
            'mirror %s %s %s',
            implode(' ', $this->getMirrorParams()),
            $this->getSourcePath(),
            $this->getDestinationPath()
        );
    }

    abstract protected function getSourcePath(): string;

    abstract protected function getDestinationPath(): string;

    protected function getOpenCommand(): string
    {
        return sprintf(
            'open %s://%s:%s@%s',
            $this->config->getRemoteProtocol(),
            $this->config->getRemoteUser(),
            $this->config->getRemotePassword(),
            $this->config->getRemoteHost()
        );
    }

    protected function getMirrorParams(): array
    {
        $result = [];
        if ($this->config->getMirrorParallels() > 0) {
            $result[] = '--parallel=' . $this->config->getMirrorParallels();
        }
        if ($this->config->getMirrorDelete()) {
            $result[] = '--delete';
        }
        return $result;
    }
}