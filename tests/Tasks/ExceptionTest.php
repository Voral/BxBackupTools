<?php

namespace Vasoft\BxBackupTools\Tasks;

use Error;
use PHPUnit\Framework\TestCase;
use Vasoft\BxBackupTools\Core\Application;
use Vasoft\BxBackupTools\Core\Exceptions\ProcessException;
use Vasoft\BxBackupTools\Core\MessageContainer;
use Vasoft\BxBackupTools\Core\Task;

class ExceptionTest extends TestCase
{

    /**
     * Если в ходе выполнения задач не возникло ошибок, не должен ни чего добавлять в сообщение
     * @return void
     */
    public function testHandleNoError()
    {
        $app = new Application(
            [
                new TestTask('Task 1', TestTask::MODE_NO_ERROR),
                new Exception(),
                new TestTask('Task 2', TestTask::MODE_NO_ERROR),
                new TestTask('Task 3', TestTask::MODE_NO_ERROR)
            ]
        );
        $messages = new MessageContainer();
        $app->handle($messages);
        $this->assertSame(
            [
                'Task 1 Begin',
                'Task 2 Begin',
                'Task 3 Begin',
                'Task 3 End',
                'Task 2 End',
                'Task 1 End',
            ],
            $messages->getStringArray()
        );
    }

    /**
     * Если в ходе выполнения задач возникает ошибка, унаследованная от ProcessException, обрабатывает и добавляет информацию об ошибке в сообщение. Дополнительные данные ошибки принимает как строку так и массив
     * @return void
     * @dataProvider dataHandleProcessError
     */
    public function testHandleProcessError(array|string $exceptionData, array $expected)
    {
        $app = new Application(
            [
                new TestTask('Task 1', TestTask::MODE_NO_ERROR),
                new Exception(),
                new TestTask('Task 2', TestTask::MODE_PROCESS_ERROR, $exceptionData),
                new TestTask('Task 3', TestTask::MODE_NO_ERROR)
            ]
        );
        $messages = new MessageContainer();
        $app->handle($messages);
        $this->assertSame($expected, $messages->getStringArray());
    }

    /**
     * Если в ходе выполнения задач возникает ошибка, не унаследованная от ProcessException, не обрабатывает ее
     * @return void
     */
    public function testHandleOtherError()
    {
        $app = new Application(
            [
                new TestTask('Task 1', TestTask::MODE_NO_ERROR),
                new Exception(),
                new TestTask('Task 2', TestTask::MODE_PHP_ERROR),
                new TestTask('Task 3', TestTask::MODE_NO_ERROR)
            ]
        );
        $messages = new MessageContainer();
        $this->expectException(Error::class);
        $this->expectExceptionMessage('PHP Error');
        $app->handle($messages);
    }

    public static function dataHandleProcessError(): array
    {
        return [
            [
                ['error string 1', 'error string 2'],
                [
                    'Task 1 Begin',
                    'Task 2 Begin',
                    'Process Error',
                    'error string 1',
                    'error string 2',
                    'Task 1 End',
                ]
            ],
            [
                'error string 1',
                [
                    'Task 1 Begin',
                    'Task 2 Begin',
                    'Process Error',
                    'error string 1',
                    'Task 1 End',
                ]
            ],
        ];
    }
}

class TestTask implements Task
{
    public const MODE_NO_ERROR = 0;
    public const MODE_PROCESS_ERROR = 1;
    public const MODE_PHP_ERROR = 2;

    public function __construct(
        private readonly string       $title,
        private readonly int          $mode,
        private readonly string|array $exceptionData = ''
    )
    {
    }

    /**
     * @param MessageContainer $message
     * @param Task|null $next
     * @return void
     * @throws TestException
     */
    public function handle(MessageContainer $message, ?Task $next = null): void
    {
        $message->add($this->title, $this->title . ' Begin');
        switch ($this->mode) {
            case self::MODE_NO_ERROR:
                break;
            case self::MODE_PROCESS_ERROR:
                throw new TestException($this->exceptionData);
            case self::MODE_PHP_ERROR:
                throw new Error('PHP Error');
        }
        $next?->handle($message);
        $message->add($this->title, $this->title . ' End');
    }
}

class TestException extends ProcessException
{
    public function __construct(string|array $data)
    {
        parent::__construct('Process Error', $data, 'test');
    }
}
