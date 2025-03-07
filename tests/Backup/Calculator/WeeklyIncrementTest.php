<?php

namespace Vasoft\BxBackupTools\Backup\Calculator {

    use PHPUnit\Framework\TestCase;

    if (!function_exists('\Vasoft\BxBackupTools\Backup\Calculator\date')) {
        $fakeTime = 0;
        function date(string $format): string
        {
            global $fakeTime;
            return \date($format, $fakeTime);
        }
    }

    class WeeklyIncrementTest extends TestCase
    {
        /**
         * @param int $time
         * @param string $expected
         * @param string $message
         * @return void
         * @dataProvider dataGetNext
         */
        public function testGetNext(int $time, string $expected, string $message): void
        {
            global $fakeTime;
            $fakeTime = $time;
            $calc = new WeeklyIncrement();
            $this->assertSame($expected, $calc->getNext('/test'), $message);
        }

        public static function dataGetNext(): array
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
    }

}