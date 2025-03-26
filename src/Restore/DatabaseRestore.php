<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Restore;

class DatabaseRestore
{
    private ?bool $hasFullTextIndex = null;
    private false|\mysqli $connection;
    /** @var false|resource */
    private $file;

    /**
     * @throws RestoreException
     */
    public function __construct(
        public readonly string $databaseHost,
        public readonly string $databaseName,
        public readonly string $databaseLogin,
        public readonly string $databasePassword,
    ) {
        if (!function_exists('mysqli_connect')) {
            throw new RestoreException('MySQLi extension is not installed', []);
        }
    }

    /**
     * @param string $fileName Путь к файлу дампа базы данных
     *
     * @throws RestoreException Если произошла ошибка при восстановлении базы данных
     */
    public function restore(string $fileName): void
    {
        clearstatcache();
        $this->onBegin($fileName);
        if (!file_exists($fileName)) {
            throw new RestoreException('File ' . $fileName . ' not found');
        }
        do {
            if (!($this->file = @fopen($fileName, 'r'))) {
                throw new RestoreException("Can't open file: " . $fileName);
            }
            while (($sql = $this->readSql()) !== '') {
                $this->query($sql);
            }
            fclose($this->file);
            $fileName = $this->getNextName($fileName);
        } while (file_exists($fileName));
        $this->onEnd();
    }

    /**
     * @throws RestoreException
     */
    private function onBegin(string $fileName): void
    {
        $this->connection = mysqli_connect(
            $this->databaseHost,
            $this->databaseLogin,
            $this->databasePassword,
            $this->databaseName,
        );
        if (!$this->connection) {
            throw new RestoreException('Can\'t connect to database', [
                'error' => mysqli_connect_error(),
            ]);
        }
        $this->query('SET FOREIGN_KEY_CHECKS = 0');
        $sqlAfterConnectFile = str_replace('.sql', '_after_connect.sql', $fileName);
        if (file_exists($sqlAfterConnectFile)) {
            $arSql = explode(';', file_get_contents($sqlAfterConnectFile));
            foreach ($arSql as $sql) {
                $sql = trim(str_replace('<DATABASE>', $this->databaseName, $sql));
                if ('' !== $sql) {
                    $this->query($sql);
                }
            }
        }
    }

    /**
     * @throws RestoreException
     */
    private function onEnd(): void
    {
        $this->query('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * @throws RestoreException
     */
    private function query(string $sql): bool|\mysqli_result
    {
        $sqlCleaned = $this->processFullTextKey($sql);
        $rs = mysqli_query($this->connection, $sqlCleaned);
        if (!$rs || mysqli_errno($this->connection)) {
            if (null === $this->hasFullTextIndex && preg_match('#^CREATE TABLE.*FULLTEXT KEY#ms', $sql)) {
                $this->hasFullTextIndex = false;

                return $this->query($sql);
            }

            throw new RestoreException(mysqli_error($this->connection));
        }

        return $rs;
    }

    private function processFullTextKey(string $sql): string
    {
        if (false === $this->hasFullTextIndex && preg_match('#^CREATE TABLE.*FULLTEXT KEY#ms', $sql)) {
            $sql = preg_replace("#[\r\n\\s]*FULLTEXT KEY[^\r\n]*[\r\n]*#m", '', $sql);
            $sql = str_replace('),)', '))', $sql);
        }

        return $sql;
    }

    private function readSql(): string
    {
        $sql = '';
        while (!str_ends_with(trim($sql), ';')) {
            $line = fgets($this->file);
            if (feof($this->file) || false === $line) {
                if (false !== $line) {
                    $sql .= $line;
                }

                return trim($sql);
            }
            $sql .= $line;
        }

        return trim($sql);
    }

    private function getNextName(string $file): string
    {
        $parts = pathinfo($file);
        $baseName = $parts['filename'];
        $extension = $parts['extension'] ?? '';
        if (preg_match('/\.(\d+)$/', $baseName, $matches)) {
            $partNumber = (int) $matches[1] + 1;
            $newBaseName = preg_replace('/\.\d+$/', '.' . $partNumber, $baseName);
        } else {
            $newBaseName = $baseName . '.1';
        }

        return $parts['dirname'] . \DIRECTORY_SEPARATOR . $newBaseName . '.' . $extension;
    }
}
