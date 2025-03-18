<?php

declare(strict_types=1);
/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Vasoft\BxBackupTools\Restore;

use PHPUnit\Framework\TestCase;

function mysqli_error(\mysqli $mysql)
{
    return 'Test database error';
}

function mysqli_errno(\mysqli $mysql)
{
    $result = (DatabaseRestoreTest::$sqlError && DatabaseRestoreTest::$sqlErrorLevel <= 0) ? 1 : 0;
    if ($result && DatabaseRestoreTest::$sqlErrorOnce) {
        DatabaseRestoreTest::$sqlError = false;
    }

    return $result;
}

function mysqli_query(
    \mysqli $mysql,
    string $query,
    int $result_mode = MYSQLI_STORE_RESULT,
): bool|\mysqli_result {
    --DatabaseRestoreTest::$sqlErrorLevel;
    if (DatabaseRestoreTest::$sqlError && DatabaseRestoreTest::$sqlErrorLevel <= 0) {
        if (DatabaseRestoreTest::$sqlErrorOnce) {
            DatabaseRestoreTest::$sqlError = false;
        }

        return false;
    }
    DatabaseRestoreTest::$sql[] = $query;

    return true;
}

function mysqli_connect(
    ?string $hostname = null,
    ?string $username = null,
    ?string $password = null,
    ?string $database = null,
    ?int $port = null,
    ?string $socket = null,
): false|\mysqli {
    return DatabaseRestoreTest::$databaseAvailable ? new \mysqli() : false;
}

function clearstatcache(bool $clear_realpath_cache = false, string $filename = ''): void
{
    ++DatabaseRestoreTest::$cacheCleanCount;
}

function function_exists(string $functionName): bool
{
    return DatabaseRestoreTest::$functionExists;
}

/**
 * @internal
 */
final class DatabaseRestoreTest extends TestCase
{
    public const DATABASE_HOST = 'databaseHost';
    public const DATABASE_NAME = 'databaseName';
    public const DATABASE_LOGIN = 'databaseLogin';
    public const DATABASE_PASSWORD = 'databasePassword';
    public static array $sql = [];
    public static bool $databaseAvailable = true;
    public static bool $functionExists = true;
    public static int $cacheCleanCount = 0;
    public static bool $sqlError = false;
    public static bool $sqlErrorOnce = false;
    public static int $sqlErrorLevel = 0;

    private string $testFileName = __DIR__ . '/test_dump.sql';
    private string $testFileNameExt = __DIR__ . '/test_dump.1.sql';
    private string $afterConnectFileName = __DIR__ . '/test_dump_after_connect.sql';

    protected function setUp(): void
    {
        file_put_contents(
            $this->testFileName,
            <<<'SQL'
                create table A
                (
                    id   int primary key auto_increment,
                    name varchar(20)
                );
                create table B
                (
                    id   int primary key auto_increment,
                    name varchar(20)
                );
                SQL,
        );
        file_put_contents($this->afterConnectFileName, 'CREATE DATABASE <DATABASE>;');
        file_put_contents($this->testFileNameExt, "insert into A (ID, NAME) values (1, 'Test;2')");

        self::$functionExists = true;
        self::$databaseAvailable = true;
        self::$cacheCleanCount = 0;
        self::$sqlError = false;
        self::$sql = [];

        parent::setUp();
    }

    protected function tearDown(): void
    {
        unlink($this->testFileName);
        unlink($this->afterConnectFileName);
        unlink($this->testFileNameExt);

        parent::tearDown();
    }

    public function testSuccessfulRestore(): void
    {
        $processor = new DatabaseRestore(
            self::DATABASE_HOST,
            self::DATABASE_NAME,
            self::DATABASE_LOGIN,
            self::DATABASE_PASSWORD,
        );
        $processor->restore($this->testFileName);
        self::assertSame(1, self::$cacheCleanCount, 'Cache should be cleaned once');
        $queries = count(self::$sql);
        self::assertEquals(6, $queries, 'Should be 6 SQL queries');
        self::assertSame(
            'SET FOREIGN_KEY_CHECKS = 0',
            self::$sql[0],
            'First query should be SET FOREIGN_KEY_CHECKS = 0',
        );
        self::assertSame(
            'SET FOREIGN_KEY_CHECKS = 1',
            self::$sql[$queries - 1],
            'First query should be SET FOREIGN_KEY_CHECKS = 1',
        );
        self::assertSame(
            'CREATE DATABASE databaseName',
            self::$sql[1],
            'Database name should be replaced from after connect script',
        );
        self::assertSame(
            'create table A
(
    id   int primary key auto_increment,
    name varchar(20)
);',
            self::$sql[2],
            'Should be query form first part of dump',
        );
        self::assertSame(
            "insert into A (ID, NAME) values (1, 'Test;2')",
            self::$sql[4],
            'Should be query form second part of dump',
        );
    }

    public function testFileDoesNotExist(): void
    {
        $databaseRestore = new DatabaseRestore(
            databaseHost: 'localhost',
            databaseName: 'test_db',
            databaseLogin: 'root',
            databasePassword: 'password',
        );
        $file = __DIR__ . '/nonexistent_file.sql';
        $this->expectException(RestoreException::class);
        $this->expectExceptionMessage("File {$file} not found");
        $databaseRestore->restore(__DIR__ . '/nonexistent_file.sql');
    }

    public function testFileAccess(): void
    {
        $databaseRestore = new DatabaseRestore(
            databaseHost: 'localhost',
            databaseName: 'test_db',
            databaseLogin: 'root',
            databasePassword: 'password',
        );

        try {
            chmod($this->testFileName, 0o222);
            $this->expectException(RestoreException::class);
            $this->expectExceptionMessage("Can't open file: ");
            $databaseRestore->restore($this->testFileName);
        } finally {
            chmod($this->testFileName, 0o644);
        }
    }

    public function testDatabaseConnectionFailure(): void
    {
        self::$databaseAvailable = false;
        $databaseRestore = new DatabaseRestore(
            self::DATABASE_HOST,
            self::DATABASE_NAME,
            self::DATABASE_LOGIN,
            self::DATABASE_PASSWORD,
        );

        $this->expectException(RestoreException::class);
        $this->expectExceptionMessage('Can\'t connect to database');

        $databaseRestore->restore($this->testFileName);
    }

    public function testSqlExecutionError(): void
    {
        self::$sqlError = true;
        self::$sqlErrorLevel = 1;
        $databaseRestore = new DatabaseRestore(
            self::DATABASE_HOST,
            self::DATABASE_NAME,
            self::DATABASE_LOGIN,
            self::DATABASE_PASSWORD,
        );
        $this->expectException(RestoreException::class);
        $this->expectExceptionMessage('Test database error');
        $databaseRestore->restore($this->testFileName);
    }

    public function testSqlExecutionErrorInLoop(): void
    {
        self::$sqlError = true;
        self::$sqlErrorLevel = 3;
        $databaseRestore = new DatabaseRestore(
            self::DATABASE_HOST,
            self::DATABASE_NAME,
            self::DATABASE_LOGIN,
            self::DATABASE_PASSWORD,
        );
        $this->expectException(RestoreException::class);
        $this->expectExceptionMessage('Test database error');
        $databaseRestore->restore($this->testFileName);
    }

    /**
     * Должен проверять наличие расширения MySQLi.
     */
    public function testConstructNotPresent(): void
    {
        self::$functionExists = false;
        $this->expectExceptionMessage('MySQLi extension is not installed');
        $this->expectException(RestoreException::class);
        new DatabaseRestore(
            self::DATABASE_HOST,
            self::DATABASE_NAME,
            self::DATABASE_LOGIN,
            self::DATABASE_PASSWORD,
        );
    }

    public function testRestoreDatabaseUnavailable(): void
    {
        $databaseRestore = new DatabaseRestore(
            self::DATABASE_HOST,
            self::DATABASE_NAME,
            self::DATABASE_LOGIN,
            self::DATABASE_PASSWORD,
        );
        self::$databaseAvailable = false;
        $this->expectExceptionMessage('Can\'t connect to database');
        $this->expectException(RestoreException::class);
        $databaseRestore->restore($this->testFileName);
    }

    public function testDatabaseNotSupportFullTextKey(): void
    {
        self::$sqlError = true;
        self::$sqlErrorLevel = 2;
        self::$sqlErrorOnce = true;

        $testFileName = __DIR__ . '/test_dump_fulltext.sql';
        file_put_contents(
            $testFileName,
            <<<'SQL'
                CREATE TABLE test (
                    id INT PRIMARY KEY,
                    name VARCHAR(255),
                    FULLTEXT KEY idx_name (name)
                );
                SQL,
        );

        try {
            $databaseRestore = new DatabaseRestore(
                self::DATABASE_HOST,
                self::DATABASE_NAME,
                self::DATABASE_LOGIN,
                self::DATABASE_PASSWORD,
            );
            $databaseRestore->restore($testFileName);
            self::assertSame(
                'CREATE TABLE test (
    id INT PRIMARY KEY,
    name VARCHAR(255));',
                self::$sql[1],
                'Second query should be the same as the first one after modification',
            );
        } finally {
            unlink($testFileName);
        }
    }
}
