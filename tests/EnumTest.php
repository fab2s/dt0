<?php

/*
 * This file is part of fab2s/Dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/Dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Tests;

use fab2s\Dt0\Property\Property;
use fab2s\Dt0\Tests\Artifacts\Enum\IntBackedEnum;
use fab2s\Dt0\Tests\Artifacts\Enum\StringBackedEnum;
use fab2s\Dt0\Tests\Artifacts\Enum\UnitEnum;
use fab2s\Dt0\Tests\Artifacts\EnumDt0;
use JsonException;
use PHPUnit\Framework\Attributes\DataProvider;

class EnumTest extends TestCase
{
    /**
     * @throws JsonException
     */
    #[DataProvider('enumProvider')]
    public function test_enum_dt0(
        array $args,
        array $expected,
    ): void {
        $keys = array_keys($args);
        shuffle($keys);
        $keys = array_flip($keys);
        $dt0  = EnumDt0::fromArray(array_filter(
            array_replace($keys, $args),
        ));

        $toArrayExpected = [];
        foreach ($expected as $prop => $value) {
            $this->assertSame($value, $dt0->$prop);
            $toArrayExpected[$prop] = $value->value ?? $value->name;
        }

        $this->assertSame($toArrayExpected, $dt0->toArray());

        $this->dt0Assertions($dt0);
    }

    public static function enumProvider(): array
    {
        $cases = [];
        $props = [
            'unitEnum'         => UnitEnum::class,
            'stringBackedEnum' => StringBackedEnum::class,
            'intBackedEnum'    => IntBackedEnum::class,
        ];

        foreach (StringBackedEnum::cases() as $case) {
            $args = [
                'unitEnum'         => Property::tryEnumFromName(UnitEnum::class, $case->name),
                'stringBackedEnum' => $case,
                'intBackedEnum'    => Property::tryEnumFromName(IntBackedEnum::class, $case->name),
            ];

            $expected = $args;
            foreach ($props as $propName => $enumFqn) {
                $withDefaultProp            = $propName . 'WithDefault';
                $args[$withDefaultProp]     = null;
                $expected[$withDefaultProp] = Property::tryEnumFromName($enumFqn, 'ONE');
            }

            $caseName         = $case->name . '_instance_default';
            $cases[$caseName] = [
                'args'     => $args,
                'expected' => $expected,
            ];

            foreach ($props as $propName => $enumFqn) {
                $withDefaultProp            = $propName . 'WithDefault';
                $args[$propName]            = $args[$propName]->value ?? $args[$propName]->name;
                $args[$withDefaultProp]     = $args[$propName];
                $expected[$withDefaultProp] = $expected[$propName];
            }

            $caseName         = $case->name . '_strings_string';
            $cases[$caseName] = [
                'args'     => $args,
                'expected' => $expected,
            ];

            foreach ($props as $propName => $enumFqn) {
                $withDefaultProp        = $propName . 'WithDefault';
                $args[$withDefaultProp] = Property::tryEnum($enumFqn, $args[$propName]);
            }

            $caseName         = $case->name . '_strings_instance';
            $cases[$caseName] = [
                'args'     => $args,
                'expected' => $expected,
            ];
        }

        return $cases;
    }
}
