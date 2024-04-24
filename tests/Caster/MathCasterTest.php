<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Tests\Caster;

use fab2s\Dt0\Caster\MathCaster;
use fab2s\Dt0\Tests\TestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

class MathCasterTest extends TestCase
{
    #[DataProvider('castProvider')]
    public function test_cast($precision, $value, $expected): void
    {
        $caster = new MathCaster($precision);

        $this->assertSame($expected, (string) $caster->cast($value));
    }

    public function test_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $caster = new MathCaster;
        $caster->cast('NaN');
    }

    public static function castProvider(): array
    {
        return [
            [
                'precision' => 0,
                'value'     => 42,
                'expected'  => '42',
            ],
            [
                'precision' => 4,
                'value'     => 42,
                'expected'  => '42',
            ],
            [
                'precision' => 4,
                'value'     => '   0000042.00000   ',
                'expected'  => '42',
            ],
        ];
    }
}
