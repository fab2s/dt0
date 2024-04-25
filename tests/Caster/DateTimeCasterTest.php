<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Tests\Caster;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use fab2s\Dt0\Caster\CarbonCaster;
use fab2s\Dt0\Caster\DateTimeCaster;
use fab2s\Dt0\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class DateTimeCasterTest extends TestCase
{
    /**
     * @throws Exception
     */
    #[DataProvider('castProvider')]
    public function test_cast(DateTimeZone|string|null $timezone, $value, $expected): void
    {
        foreach ([DateTimeCaster::class, CarbonCaster::class] as $casterClass) {
            foreach ([true, false] as $immutable) {
                $caster = new $casterClass($timezone, $immutable);
                $casted = $caster->cast($value);
                if ($casterClass === DateTimeCaster::class) {
                    if ($immutable) {
                        if ($value !== null) {
                            $this->assertInstanceOf(DateTimeImmutable::class, $casted);
                        }
                    } else {
                        if ($value !== null) {
                            $this->assertInstanceOf(DateTime::class, $casted);
                        }
                    }
                } else {
                    if ($immutable) {
                        if ($value !== null) {
                            $this->assertInstanceOf(CarbonImmutable::class, $casted);
                        }
                    } else {
                        if ($value !== null) {
                            $this->assertInstanceOf(Carbon::class, $casted);
                        }
                    }
                }

                $this->assertSame($expected, $casted?->format('Y-m-d H:i'), "Using $casterClass in " . ($immutable ? 'immutable' : 'mutable') . 'mode');
            }
        }

    }

    public static function castProvider(): array
    {
        return [
            [
                'timezone' => null,
                'value'    => 'now',
                'expected' => (new DateTime('now'))->format('Y-m-d H:i'),
            ],
            [
                'timezone' => 'UTC',
                'value'    => 'now + 1 day',
                'expected' => (new DateTime('now + 1 day'))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i'),
            ],
            [
                'timezone' => 'Antarctica/Troll',
                'value'    => '1337-01-01 13:37:00',
                'expected' => (new DateTime('1337-01-01 13:37:00'))->setTimezone(new DateTimeZone('Antarctica/Troll'))->format('Y-m-d H:i'),
            ],
            [
                'timezone' => null,
                'value'    => null,
                'expected' => null,
            ],
        ];
    }
}
