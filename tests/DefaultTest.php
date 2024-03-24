<?php

/*
 * This file is part of fab2s/Dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/Dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Tests;

use fab2s\Dt0\Tests\Artifacts\DefaultDt0;
use fab2s\Dt0\Tests\Artifacts\SimpleDefaultDt0;
use JsonException;
use PHPUnit\Framework\Attributes\DataProvider;

class DefaultTest extends TestCase
{
    #[DataProvider('simpleDefaultProvider')]
    public function test_simple_default_dt0(array $args)
    {
        $expected = $args;
        $dt0      = SimpleDefaultDt0::fromArray(array_filter($args));
        foreach ($args as $key => $value) {
            if ($value === null) {
                $expected[$key] = 'default';
            }
        }

        $this->assertSame($expected, $dt0->toArray());
    }

    public static function simpleDefaultProvider(): array
    {
        return [
            [
                [
                    'stringNoCast'      => 'assigned',
                    'stringCast'        => null,
                    'stringCastDefault' => null,
                ],
            ],
            [
                [
                    'stringNoCast'      => 'assigned',
                    'stringCast'        => 'assigned',
                    'stringCastDefault' => null,
                ],
            ],
            [
                [
                    'stringNoCast'      => 'assigned',
                    'stringCast'        => 'assigned',
                    'stringCastDefault' => 'assigned',
                ],
            ],
        ];
    }

    /**
     * @throws JsonException
     */
    #[DataProvider('defaultProvider')]
    public function test_default_dt0(
        array $args,
        array $expected,
    ): void {
        $keys = array_keys($args);
        shuffle($keys);
        $keys = array_flip($keys);
        $args = array_filter(
            array_replace($keys, $args),
        );

        foreach ($args as $name => $value) {
            if ($value === 'null') {
                $args[$name] = null;
            }
        }

        $dt0 = DefaultDt0::fromArray($args);

        $this->assertSame($expected, $dt0->toArray());
        $this->dt0Assertions($dt0);
    }

    public static function defaultProvider(): array
    {
        $cases = [];
        $props = [
            'stringNoCast'                 => ['assigned'],
            'stringCast'                   => ['assigned'],
            'stringCastDefault'            => [null, 'assigned'],
            'stringCastDefaultNull'        => [null, 'null', 'assigned'],
            'stringNoCastDefault'          => [null, 'assigned'],
            'stringDefaultCastDefault'     => [null, 'assigned'],
            'stringDefaultNullCastDefault' => [null, 'null', 'assigned'],
            'stringDefaultCastDefaultNull' => [null, 'null', 'assigned'],
        ];

        $expected = [
            'stringNoCast'                 => ['assigned'],
            'stringCast'                   => ['assigned'],
            'stringCastDefault'            => ['casted', 'assigned'],
            'stringCastDefaultNull'        => [null, null, 'assigned'],
            'stringNoCastDefault'          => ['default', 'assigned'],
            'stringDefaultCastDefault'     => ['casted', 'assigned'],
            'stringDefaultNullCastDefault' => ['casted', null, 'assigned'],
            'stringDefaultCastDefaultNull' => [null, null, 'assigned'],
        ];

        $cases       = [];
        $defaultArgs = [];
        foreach ($props as $prop => $values) {
            $defaultArgs[$prop] = $values[0];
        }

        $defaultExpected = [];
        foreach ($expected as $prop => $values) {
            $defaultExpected[$prop] = $values[0];
        }

        foreach ($props as $prop => $values) {
            $case = [
                'args'     => $defaultArgs,
                'expected' => $defaultExpected,
            ];

            foreach ($values as $idx => $value) {
                $case['args'][$prop]     = $value;
                $case['expected'][$prop] = $expected[$prop][$idx];
                $cases[]                 = $case;
            }
        }

        return $cases;
    }
}
