<?php

declare(strict_types=1);

namespace Vasoft\BxBackupTools\Backup\Calculator;

use PHPUnit\Framework\TestCase;

if (!function_exists('\Vasoft\BxBackupTools\Backup\Calculator\date')) {
    $fakeTime = 0;
    function date(string $format): string
    {
        global $fakeTime;

        return \date($format, $fakeTime);
    }
}

/**
 * @internal
 *
 * @coversDefaultClass \Vasoft\BxBackupTools\Backup\Calculator\WeeklyIncrement
 */
final class WeeklyIncrementTest extends TestCase
{
    public static function provideGetNextCases(): iterable
    {
        return [
            [mktime(0, 0, 0, 1, 20, 2025), '/test1', 'Monday'],
            [mktime(0, 0, 0, 1, 21, 2025), '/test2', 'Tuesday'],
            [mktime(0, 0, 0, 1, 22, 2025), '/test3', 'Wednesday'],
            [mktime(0, 0, 0, 1, 23, 2025), '/test4', 'Thursday'],
            [mktime(0, 0, 0, 1, 24, 2025), '/test5', 'Friday'],
            [mktime(0, 0, 0, 1, 25, 2025), '/test6', 'Saturday'],
            [mktime(0, 0, 0, 1, 26, 2025), '/test0', 'Sunday'],
        ];
    }

    /**
     * @dataProvider provideGetNextCases
     */
    public function testGetNext(int $time, string $expected, string $message): void
    {
        global $fakeTime;
        $fakeTime = $time;
        $calc = new WeeklyIncrement();
        self::assertSame($expected, $calc->getNext('/test'), $message);
    }
}
