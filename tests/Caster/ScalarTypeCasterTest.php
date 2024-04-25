<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Tests\Caster;

use fab2s\Dt0\Caster\ScalarType;
use fab2s\Dt0\Caster\ScalarTypeCaster;
use fab2s\Dt0\Exception\CasterException;
use fab2s\Dt0\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ScalarTypeCasterTest extends TestCase
{
    /**
     * @throws CasterException
     */
    #[DataProvider('castProvider')]
    public function test_cast($type, $value, $expected): void
    {
        $caster = new ScalarTypeCaster($type);

        $this->assertSame($expected, $caster->cast($value));
    }

    public function test_exception(): void
    {
        $this->expectException(CasterException::class);
        new ScalarTypeCaster('notAScalarType');
    }

    public static function castProvider(): array
    {
        return [
            [
                'type'     => 'string',
                'value'    => 42,
                'expected' => '42',
            ],
            [
                'type'     => ScalarType::bool,
                'value'    => 42,
                'expected' => true,
            ],
            [
                'type'     => ScalarType::int,
                'value'    => '42.42',
                'expected' => 42,
            ],
            [
                'type'     => ScalarType::null,
                'value'    => '42.42',
                'expected' => null,
            ],
            [
                'type'     => ScalarType::float,
                'value'    => [],
                'expected' => null,
            ],
            [
                'type'     => ScalarType::null,
                'value'    => 'anything',
                'expected' => null,
            ],
            [
                'type'     => ScalarType::bool,
                'value'    => null,
                'expected' => false,
            ],
        ];
    }
}
