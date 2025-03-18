<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Core;

use PHPUnit\Framework\TestCase;

function exec(string $command, &$output, &$result_code): false|string
{
    global $lastCommand, $execCount;
    $output = 'Output Changed';
    $result_code = 'Result Code Changed';
    $lastCommand = $command;
    ++$execCount;

    return '';
}

/**
 * @internal
 * @coversDefaultClass \Vasoft\BxBackupTools\Core\SystemCmd
 */
final class SystemCmdTest extends TestCase
{
    /**
     * Должна быть вызвана функция exec с заданным параметром 1 раз.
     * Параметры преданные по ссылке, должны прокидываться в функцию exec.
     */
    public function testExec(): void
    {
        global $execCount, $lastCommand;
        $execCount = 0;
        $lastCommand = '';
        $output = $resultCode = null;
        $cmd = new SystemCmd();
        $cmd->exec('ls -la', $output, $resultCode);
        $this->assertEquals('ls -la', $lastCommand);
        $this->assertEquals(1, $execCount);
        $this->assertEquals('Output Changed', $output);
        $this->assertEquals('Result Code Changed', $resultCode);
    }
}
