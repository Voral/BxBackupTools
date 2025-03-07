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

    class WeeklyIncrementPreviousTest extends TestCase
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
            $calc = new WeeklyIncrementPrevious();
            $this->assertSame($expected, $calc->getNext('/test/bkp'), $message);
        }

        public static function dataGetNext(): array
        {
            return [
                [mktime(0, 0, 0, 1, 20, 2025), '/test/bkp0', 'Monday'],
                [mktime(0, 0, 0, 1, 21, 2025), '/test/bkp1', 'Tuesday'],
                [mktime(0, 0, 0, 1, 22, 2025), '/test/bkp2', 'Wednesday'],
                [mktime(0, 0, 0, 1, 23, 2025), '/test/bkp3', 'Thursday'],
                [mktime(0, 0, 0, 1, 24, 2025), '/test/bkp4', 'Friday'],
                [mktime(0, 0, 0, 1, 25, 2025), '/test/bkp5', 'Saturday'],
                [mktime(0, 0, 0, 1, 26, 2025), '/test/bkp6', 'Sunday'],
            ];
        }
    }
}