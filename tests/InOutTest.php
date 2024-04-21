<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Tests;

use DateTime;
use DateTimeImmutable;
use Exception;
use fab2s\Dt0\Caster\DateTimeCaster;
use fab2s\Dt0\Property\Properties;
use fab2s\Dt0\Tests\Artifacts\DefaultDt0;
use fab2s\Dt0\Tests\Artifacts\InOutDt0;
use JsonException;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionException;

class InOutTest extends TestCase
{
    /**
     * @throws ReflectionException
     * @throws Exception
     */
    #[DataProvider('inOutProvider')]
    public function test_in_out_dt0(array $args)
    {
        $expected   = $args;
        $args       = array_filter($args);
        $caster     = new DateTimeCaster;
        $properties = Properties::make(InOutDt0::class);
        foreach ($args as $key => $value) {
            if ($value === 'null') {
                $args[$key]     = null;
                $expected[$key] = null;

                continue;
            }

            if (is_string($value) || is_int($value)) {
                $expected[$key] = $caster->cast($value);
            }

            if ($properties->get($key)?->cast?->out) {
                $expected[$key] = $properties->get($key)->cast->out->cast($value);
            }
        }

        $dt0 = InOutDt0::fromArray($args);

        $this->assertSame(json_encode($expected), $dt0->toJson());

        $this->dt0Assertions($dt0);
    }

    public static function inOutProvider(): array
    {
        $props = [
            'classCastedIn'          => ['null', '2042-12-31 23:59:59', new DateTime(), time()],
            'classCastedInOut'       => ['null', '2042-12-31 23:59:59', new DateTime(), time()],
            'castedOut'              => ['null', new DateTimeImmutable()],
            'classCastedPromotedOut' => ['null', new DateTimeImmutable()],
            'castedPromotedInOut'    => [null, 'null', '2042-12-31 23:59:59', new DateTime(), time()],
        ];

        $cases = [];

        $baseCase = [];
        foreach ($props as $name => $values) {
            $baseCase[$name] = $values[0];
        }

        foreach ($props as $name => $values) {
            foreach ($values as $value) {
                $baseCase[$name] = $value;
                $cases[]         = [$baseCase];
            }
        }

        return $cases;
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
