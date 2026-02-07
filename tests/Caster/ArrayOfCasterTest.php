<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace Tests\Caster;

use Exception;
use fab2s\Dt0\Caster\ArrayOfCaster;
use fab2s\Dt0\Caster\ScalarType;
use fab2s\Dt0\Exception\CasterException;
use fab2s\Dt0\Exception\Dt0Exception;
use JsonException;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionException;
use Tests\Artifacts\Enum\IntBackedEnum;
use Tests\Artifacts\Enum\StringBackedEnum;
use Tests\Artifacts\Enum\UnitEnum;
use Tests\Artifacts\EnumDt0;
use Tests\TestCase;

class ArrayOfCasterTest extends TestCase
{
    /**
     * @throws Exception
     */
    #[DataProvider('castProvider')]
    public function test_cast(ScalarType|string $type, $value, $expected): void
    {
        $caster = ArrayOfCaster::make($type);

        $this->assertSame(json_encode($expected), json_encode($caster->cast($value)));
    }

    public function test_exception(): void
    {
        $this->expectException(CasterException::class);
        ArrayOfCaster::make('NotAType');
    }

    /**
     * @throws CasterException
     * @throws JsonException|ReflectionException
     */
    public function test_scalar_exception(): void
    {
        $this->expectException(Dt0Exception::class);
        $caster = ArrayOfCaster::make(ScalarType::bool);
        $caster->cast([[]]);
    }

    /**
     * @throws Dt0Exception|ReflectionException
     */
    public static function castProvider(): array
    {
        return [
            [
                'type'  => EnumDt0::class,
                'value' => [
                    EnumDt0::make(unitEnum: UnitEnum::ONE, stringBackedEnum: StringBackedEnum::ONE, intBackedEnum: IntBackedEnum::ONE),
                    ['unitEnum' => UnitEnum::TWO, 'stringBackedEnum' => StringBackedEnum::TWO, 'intBackedEnum' => IntBackedEnum::TWO],
                    '{"unitEnum":"three","stringBackedEnum":"three","intBackedEnum":3}',
                ],
                'expected' => [
                    EnumDt0::make(unitEnum: UnitEnum::ONE, stringBackedEnum: StringBackedEnum::ONE, intBackedEnum: IntBackedEnum::ONE),
                    EnumDt0::make(unitEnum: UnitEnum::TWO, stringBackedEnum: StringBackedEnum::TWO, intBackedEnum: IntBackedEnum::TWO),
                    EnumDt0::make(unitEnum: UnitEnum::three, stringBackedEnum: StringBackedEnum::three, intBackedEnum: IntBackedEnum::three),
                ],
            ],
            [
                'type'  => UnitEnum::class,
                'value' => [
                    UnitEnum::ONE,
                    'TWO',
                    'three',
                ],
                'expected' => [
                    UnitEnum::ONE,
                    UnitEnum::TWO,
                    UnitEnum::three,
                ],
            ],
            [
                'type'  => StringBackedEnum::class,
                'value' => [
                    StringBackedEnum::ONE,
                    'TWO',
                    'three',
                ],
                'expected' => [
                    StringBackedEnum::ONE,
                    StringBackedEnum::TWO,
                    StringBackedEnum::three,
                ],
            ],
            [
                'type'  => 'int',
                'value' => [
                    null,
                    '42',
                    42.42,
                    '1337.1337',
                ],
                'expected' => [
                    0,
                    42,
                    42,
                    1337,
                ],
            ],
            [
                'type'  => ScalarType::float,
                'value' => [
                    null,
                    '42',
                    42.42,
                    '1337.1337',
                ],
                'expected' => [
                    0,
                    42,
                    42.42,
                    1337.1337,
                ],
            ],
            [
                'type'     => ScalarType::bool,
                'value'    => null,
                'expected' => null,
            ],
        ];
    }
}
