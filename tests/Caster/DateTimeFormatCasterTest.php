<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Tests\Caster;

use DateTime;
use DateTimeZone;
use Exception;
use fab2s\Dt0\Caster\DateTimeFormatCaster;
use fab2s\Dt0\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class DateTimeFormatCasterTest extends TestCase
{
    /**
     * @throws Exception
     */
    #[DataProvider('castProvider')]
    public function test_cast(string $format, DateTimeZone|string|null $timezone, $value, $expected): void
    {
        $caster = new DateTimeFormatCaster($format, $timezone);
        $this->assertSame($expected, $caster->cast($value));
    }

    public static function castProvider(): array
    {
        return [
            [
                'format'   => 'Y-m-d H:i',
                'timezone' => null,
                'value'    => new DateTime('now'),
                'expected' => (new DateTime('now'))->format('Y-m-d H:i'),
            ],
            [
                'format'   => 'Y-m-d H:i',
                'timezone' => 'UTC',
                'value'    => new DateTime('now + 1 day'),
                'expected' => (new DateTime('now + 1 day'))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i'),
            ],
            [
                'format'   => 'Y-m-d H:i',
                'timezone' => 'Antarctica/Troll', // u mad bro ?
                'value'    => new DateTime('1337-01-01 13:37:00'),
                'expected' => (new DateTime('1337-01-01 13:37:00'))->setTimezone(new DateTimeZone('Antarctica/Troll'))->format('Y-m-d H:i'),
            ],
            [
                'format'   => 'Y-m-d H:i',
                'timezone' => null,
                'value'    => null,
                'expected' => null,
            ],
        ];
    }
}
