<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace Tests\Caster;

use fab2s\Dt0\Caster\JsonCaster;
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Exception\Dt0Exception;
use JsonException;
use ReflectionException;
use Tests\Artifacts\JsonTestDt0;
use Tests\TestCase;

class JsonCasterTest extends TestCase
{
    /**
     * @throws JsonException
     */
    public function test_decode_on_input(): void
    {
        $caster = JsonCaster::make();
        $json   = '{"key":"value","nested":{"a":1}}';

        // Input context: $data is array
        $result = $caster->cast($json, []);

        $this->assertSame(['key' => 'value', 'nested' => ['a' => 1]], $result);
    }

    /**
     * @throws JsonException
     */
    public function test_decode_as_object(): void
    {
        $caster = new JsonCaster(associative: false);
        $json   = '{"key":"value"}';

        $result = $caster->cast($json, []);

        $this->assertIsObject($result);
        $this->assertSame('value', $result->key);
    }

    /**
     * @throws ReflectionException
     * @throws Dt0Exception
     * @throws JsonException
     */
    public function test_encode_on_output(): void
    {
        $caster = JsonCaster::make();
        $data   = ['key' => 'value', 'nested' => ['a' => 1]];
        $dt0    = JsonTestDt0::make(metadata: $data);

        // Output context: $data is Dt0
        $result = $caster->cast($data, $dt0);

        $this->assertSame('{"key":"value","nested":{"a":1}}', $result);
    }

    /**
     * @throws JsonException
     */
    public function test_null_passthrough(): void
    {
        $caster = JsonCaster::make();

        $this->assertNull($caster->cast(null, []));
        $this->assertNull($caster->cast(null, $this->createMock(Dt0::class)));
    }

    /**
     * @throws JsonException
     */
    public function test_invalid_json_throws_exception(): void
    {
        $caster = JsonCaster::make();

        $this->expectException(JsonException::class);
        $caster->cast('not valid json', []);
    }

    /**
     * @throws JsonException
     */
    public function test_array_passthrough_on_input(): void
    {
        $caster = JsonCaster::make();
        $data   = ['already' => 'decoded'];

        // If array is passed on input, return as-is
        $result = $caster->cast($data, []);

        $this->assertSame($data, $result);
    }

    /**
     * @throws JsonException
     */
    public function test_invalid_value_on_output(): void
    {
        $caster = JsonCaster::make();
        $dt0    = $this->createMock(Dt0::class);

        // Non-array/object on output returns null
        $this->assertNull($caster->cast('string', $dt0));
        $this->assertNull($caster->cast(42, $dt0));
    }

    /**
     * @throws ReflectionException
     * @throws Dt0Exception
     */
    public function test_full_roundtrip(): void
    {
        $input = ['metadata' => '{"tags":["a","b"],"count":42}'];

        $dt0 = JsonTestDt0::fromArray($input);

        // Input was decoded
        $this->assertSame(['tags' => ['a', 'b'], 'count' => 42], $dt0->metadata);

        // Output is encoded
        $output = $dt0->toJsonArray();
        $this->assertSame('{"tags":["a","b"],"count":42}', $output['metadata']);
    }

    /**
     * @throws ReflectionException
     * @throws Dt0Exception
     */
    public function test_roundtrip_with_array_input(): void
    {
        // Input can also be an array directly (not just JSON string)
        $input = ['metadata' => ['tags' => ['a', 'b'], 'count' => 42]];

        $dt0 = JsonTestDt0::fromArray($input);

        $this->assertSame(['tags' => ['a', 'b'], 'count' => 42], $dt0->metadata);

        $output = $dt0->toJsonArray();
        $this->assertSame('{"tags":["a","b"],"count":42}', $output['metadata']);
    }
}
