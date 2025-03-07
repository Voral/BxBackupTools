<?php

namespace Vasoft\BxBackupTools\Restore;

use mysqli;
use mysqli_result;

class DatabaseRestore
{
    private bool|null $hasFullTextIndex = null;
    private false|mysqli $connection;
    private $file;
    private string $sql = '';
    private bool $eof = false;

    /**
     * @param string $databaseHost
     * @param string $databaseName
     * @param string $databaseLogin
     * @param string $databasePassword
     * @throws RestoreException
     */
    public function __construct(
        public readonly string $databaseHost,
        public readonly string $databaseName,
        public readonly string $databaseLogin,
        public readonly string $databasePassword

    )
    {
        if (!function_exists('mysqli_connect')) {
            throw new RestoreException('MySQLi extension is not installed', []);
        }
    }

    /**
     * @param string $fileName Путь к файлу дампа базы данных
     * @throws RestoreException Если произошла ошибка при восстановлении базы данных
     */
    public function restore(string $fileName): void
    {
        clearstatcache();
        $this->onBegin($fileName);
        if (!file_exists($fileName)) {
            throw new RestoreException("Can't open file: " . $fileName);
        }
        do {
            if (!($this->file = fopen($fileName, 'rb'))) {
                throw new RestoreException("Can't open file: " . $fileName);
            }
            $this->eof = false;
            while (($sql = $this->readSql()) !== '' && !$this->eof) {
                $this->query($sql);
            }
            fclose($this->file);
            $fileName = $this->getNextName($fileName);
        } while (file_exists($fileName));
        $this->onEnd();
    }

    /**
     * @param string $fileName
     * @return void
     * @throws RestoreException
     */
    private function onBegin(string $fileName): void
    {
        $this->connection = mysqli_connect($this->databaseHost, $this->databaseLogin, $this->databasePassword, $this->databaseName);
        if (!$this->connection) {
            throw new RestoreException('Can\'t connect to database', [
                'error' => mysqli_connect_error()
            ]);
        }
        $this->query('SET FOREIGN_KEY_CHECKS = 0');
        $sqlAfterConnectFile = str_replace('.sql', '_after_connect.sql', $fileName);
        if (file_exists($sqlAfterConnectFile)) {
            $arSql = explode(';', file_get_contents($sqlAfterConnectFile));
            foreach ($arSql as $sql) {
                $sql = trim(str_replace('<DATABASE>', $this->databaseName, $sql));
                if ($sql !== '') {
                    $this->query($sql);
                }
            }
        }
    }

    /**
     * @return void
     * @throws RestoreException
     */
    private function onEnd(): void
    {
        $this->query('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * @param string $sql
     * @return bool|mysqli_result
     * @throws RestoreException
     */
    private function query(string $sql): bool|mysqli_result
    {
        $sqlCleaned = $this->processFullTextKey($sql);
        $rs = mysqli_query($this->connection, $sqlCleaned);
//        echo __METHOD__,'  ',__LINE__,PHP_EOL;
        if (!$rs || mysqli_errno($this->connection)) {
//            echo __METHOD__,'  ',__LINE__,PHP_EOL;
            if ($this->hasFullTextIndex === null && preg_match("#^CREATE TABLE.*FULLTEXT KEY#ms", $sql)) {
//                echo __METHOD__,'  ',__LINE__,PHP_EOL;
                $this->hasFullTextIndex = false;
                return $this->query($sql);
            }
            throw new RestoreException(mysqli_error($this->connection));
        }
        return $rs;
    }

    private function processFullTextKey(string $sql): string
    {
        if ($this->hasFullTextIndex === false && preg_match("#^CREATE TABLE.*FULLTEXT KEY#ms", $sql)) {
            $sql = preg_replace("#[\r\n\s]*FULLTEXT KEY[^\r\n]*[\r\n]*#m", "", $sql);
            $sql = str_replace("),)", "))", $sql);
        }
        return $sql;
    }

    private function readSql(): string
    {
        $sql = '';
        while (!str_ends_with(trim($sql), ';')) {
            $line = fgets($this->file);
            if (feof($this->file) || $line === false) {
                if ($line !== false) {
                    $sql .= $line;
                }
                return trim($sql);
            }
            $sql .= $line;
        }
        return trim($sql);
    }

    protected function getNextName(string $file): string
    {
        $parts = pathinfo($file);
        $baseName = $parts['filename'];
        $extension = $parts['extension'] ?? '';
        if (preg_match('/\.(\d+)$/', $baseName, $matches)) {
            $partNumber = (int)$matches[1] + 1;
            $newBaseName = preg_replace('/\.\d+$/', '.' . $partNumber, $baseName);
        } else {
            $newBaseName = $baseName . '.1';
        }

        return $parts['dirname'] . DIRECTORY_SEPARATOR . $newBaseName . '.' . $extension;
    }
}