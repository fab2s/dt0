<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace Tests;

use fab2s\Dt0\Exception\Dt0Exception;
use fab2s\Enumerate\Enumerate;
use JsonException;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionException;
use Tests\Artifacts\Enum\IntBackedEnum;
use Tests\Artifacts\Enum\StringBackedEnum;
use Tests\Artifacts\Enum\UnitEnum;
use Tests\Artifacts\EnumDt0;

class EnumTest extends TestCase
{
    /**
     * @throws JsonException|Dt0Exception|ReflectionException
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

        $this->assertSame($toArrayExpected, $dt0->toJsonArray());

        $this->dt0Assertions($dt0);
    }

    /**
     * @throws ReflectionException
     */
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
                'unitEnum'         => Enumerate::tryFromName(UnitEnum::class, $case->name),
                'stringBackedEnum' => $case,
                'intBackedEnum'    => Enumerate::tryFromName(IntBackedEnum::class, $case->name),
            ];

            $expected = $args;
            foreach ($props as $propName => $enumFqn) {
                $withDefaultProp            = $propName . 'WithDefault';
                $args[$withDefaultProp]     = null;
                $expected[$withDefaultProp] = Enumerate::tryFromName($enumFqn, 'ONE');
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
                $args[$withDefaultProp] = Enumerate::tryFromAny($enumFqn, $args[$propName]);
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
