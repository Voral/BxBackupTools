<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Restore;

use Vasoft\BxBackupTools\Core\Exceptions\ProcessException;
use Vasoft\BxBackupTools\Core\MessageContainer;
use Vasoft\BxBackupTools\Core\System;
use Vasoft\BxBackupTools\Core\Task;

final class BitrixRestore implements Task
{
    public const MODULE_ID = 'BitrixRestore';
    private array $errors = [];
    private array $messages = [];
    private bool $encoded = false;
    private bool $zipped = false;

    public function __construct(
        protected readonly System $cmd,
        protected readonly Config $config,
    ) {}

    /**
     * @throws ProcessException
     * @throws RestoreException
     */
    public function handle(MessageContainer $message, ?Task $next = null): void
    {
        $next?->handle($message);
        $fileName = $this->getFileName();
        $tempDir = $this->getTempDirectory();
        if ($this->config->isUnpackEnabled()) {
            $this->cleanDirectory($tempDir . '/*');
            $this->unpack($fileName, $tempDir);
        }
        if ($this->config->isRestoreDatabaseEnabled()) {
            $this->restoreDatabase($fileName, $tempDir);
        }
        if ($this->config->isRestoreCreditsEnabled()) {
            $this->replaceCredits();
        }
        if ($this->config->isRestoreFilesEnabled()) {
            $this->sync();
        }
        $this->writeLog($message);
        $message->add(self::MODULE_ID, 'Restore completed');
    }

    private function log(string $message): void
    {
        $this->messages[] = sprintf('[%s] %s', date('H:i:s'), $message);
    }

    /**
     * @throws RestoreException
     * @throws ProcessException
     */
    private function restoreDatabase(string $fileName, string $tempDir): void
    {
        $fileNameSingle = $this->getFileNameSimple($fileName);
        $queryDir = $tempDir . '/bitrix/backup/';
        $fileSQL = $queryDir . $fileNameSingle . '.sql';
        (new DatabaseRestore(
            $this->config->getDatabaseHost(),
            $this->config->getDatabaseName(),
            $this->config->getDatabaseUser(),
            $this->config->getDatabasePassword(),
        ))->restore($fileSQL);
        $this->log(sprintf('Restored database %s', $fileNameSingle));
    }

    private function writeLog(MessageContainer $message): void
    {
        if (!empty($this->messages) && $this->config->isLoggingEnabled()) {
            $message->add(self::MODULE_ID, $this->messages);
        }
    }

    /**
     * @throws RestoreException
     */
    private function sync(): void
    {
        $srcDir = $this->getTempDirectory();
        $dstDir = $this->config->getSiteDocumentRoot();
        $command = sprintf("rsync -a --delete --include='.*' --recursive %s/ %s", $srcDir, $dstDir);
        $this->exec($command, 'Filesystem synchronization cannot be performed');
        $this->log('Filesystem synchronization completed');
    }

    /**
     * @throws RestoreException
     */
    private function replaceCredits(): void
    {
        $tempDir = $this->getTempDirectory();
        $file = $tempDir . '/bitrix/php_interface/dbconn.php';
        $content = file_get_contents($file);
        $content = preg_replace(
            '#\$DBHost ?= ?[\'"][^\'"]*[\'"];#',
            sprintf('$DBHost = "%s";', $this->config->getDatabaseHost()),
            $content,
        );
        $content = preg_replace(
            '#\$DBName ?= ?[\'"][^\'"]*[\'"];#',
            sprintf('$DBName = "%s";', $this->config->getDatabaseName()),
            $content,
        );
        $content = preg_replace(
            '#\$DBLogin ?= ?[\'"][^\'"]*[\'"];#',
            sprintf('$DBLogin = "%s";', $this->config->getDatabaseUser()),
            $content,
        );
        $content = preg_replace(
            '#\$DBPassword ?= ?[\'"][^\'"]*[\'"];#',
            sprintf('$DBPassword = "%s";', $this->config->getDatabasePassword()),
            $content,
        );

        try {
            file_put_contents($file, $content);
            $file = $tempDir . '/bitrix/.settings.php';
            /** @var array $content */
            $config = require_once $file;
            if (isset($config['connections']['value']['default'])) {
                $config['connections']['value']['default']['host'] = $this->config->getDatabaseHost();
                $config['connections']['value']['default']['login'] = $this->config->getDatabaseUser();
                $config['connections']['value']['default']['password'] = $this->config->getDatabasePassword();
                $config['connections']['value']['default']['database'] = $this->config->getDatabaseName();
                file_put_contents($file, '<' . "?php\nreturn " . var_export($config, true) . ';');
                $this->log('Replaced database credentials in config files');
            }
        } catch (\Exception $e) {
            new RestoreException($e->getMessage());
        }
    }

    /**
     * @throws RestoreException
     */
    private function unpack(string $fileName, string $tempDir): void
    {
        $fileFilter = preg_replace('#gz$#', '', $fileName);
        $commandTemplate = $this->encoded
            ? $this->getUnzipEncodedCommandTemplate()
            : $this->getUnzipCommandTemplate();
        $command = sprintf($commandTemplate, $fileFilter, $tempDir);
        $fileNameSimple = $this->getFileNameSimple($fileName);
        $this->exec($command, 'Impossible to unzip ' . $fileNameSimple);
        $this->log(sprintf('Unpacked %s', $fileNameSimple));
    }

    private function getFileNameSimple(string $archivePath): string
    {
        $matches = [];
        preg_match('#([^/]+?)(?:\.(tar\.gz|tar|enc\.gz|enc))?$#', $archivePath, $matches);

        return $matches[1] ?? $archivePath;
    }

    private function getUnzipEncodedCommandTemplate(): string
    {
        return
            sprintf(
                $this->zipped
                    ? "cat `ls -1v %%s*` | gunzip | tail -c +513 | openssl aes-256-ecb -d -in - -out - -K '%s' -nosalt -nopad | tar xf - -C %%s 2>&1"
                    : "cat `ls -1v %%s*` | tail -c +513 | openssl aes-256-ecb -d -in - -out - -K '%s' -nosalt -nopad | tar xf - -C %%s 2>&1",
                bin2hex(md5($this->config->getArchivePassword())),
            );
    }

    private function getUnzipCommandTemplate(): string
    {
        return $this->zipped
            ? 'cat `ls -1v %s*` | tar xzf - -C %s 2>&1'
            : 'cat `ls -1v %s*` | tar xf - -C %s 2>&1';
    }

    /**
     * @throws RestoreException
     */
    private function cleanDirectory(string $directory): void
    {
        if ($directory === '' || $directory === './' || $directory === '/') {
            throw new RestoreException('Impossible to clean temporary directory');
        }
        $this->exec("rm -rf {$directory}", 'Impossible to clean temporary directory');
        $this->log('Temporary directory cleaned');
    }

    /**
     * @throws RestoreException
     */
    private function getTempDirectory(): string
    {
        $path = $this->config->getArchivePath() . '/tmp';
        if (!file_exists($path) || !is_dir($path)) {
            if (!mkdir($path, 0o755, true)) {
                throw new RestoreException('Impossible to create directory ' . $path);
            }
        }

        return $path;
    }

    /**
     * @throws RestoreException
     */
    private function exec(string $command, string $errorMessage): void
    {
        $result = $output = null;
        $this->cmd->exec($command, $output, $result);
        if ($result !== 0) {
            if (empty($output)) {
                $errors = [$errorMessage];
            } else {
                $errors = array_merge($this->errors, $output);
            }

            throw new RestoreException(implode("\n", $errors));
        }
    }

    /**
     * @throws RestoreException
     */
    private function getFileName(): string
    {
        $fileName = $this->findNewestFile();
        $fileNameParts = explode('.', $fileName);
        if (end($fileNameParts) === 'gz') {
            $this->zipped = true;
            array_pop($fileNameParts);
        } else {
            $this->zipped = false;
        }
        if (end($fileNameParts) === 'enc') {
            $this->encoded = true;
        } else {
            $this->encoded = false;
        }

        return $fileName;
    }

    /**
     * @throws RestoreException
     */
    private function findNewestFile(): string
    {
        $directory = $this->config->getArchivePath();
        $files = array_merge(
            glob($directory . \DIRECTORY_SEPARATOR . '*.tar.gz'),
            glob($directory . \DIRECTORY_SEPARATOR . '*.tar'),
            glob($directory . \DIRECTORY_SEPARATOR . '*.enc'),
            glob($directory . \DIRECTORY_SEPARATOR . '*.enc.gz'),
        );
        if (empty($files)) {
            throw new RestoreException('Impossible to find archive');
        }
        $newestFile = '';
        $newestTime = 0;
        foreach ($files as $file) {
            $fileTime = filemtime($file);
            if ($fileTime > $newestTime) {
                $newestFile = $file;
                $newestTime = $fileTime;
            }
        }

        return $newestFile;
    }
}
