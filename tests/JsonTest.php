<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace Tests;

use fab2s\Dt0\Json;
use JsonException;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;

class JsonTest extends TestCase
{
    /**
     * @throws JsonException
     */
    #[DataProvider('encodeProvider')]
    public function test_encode(mixed $data, string $expected): void
    {
        $this->assertSame($expected, Json::encode($data));
    }

    /**
     * @throws JsonException
     */
    public function test_encode_with_flags(): void
    {
        $data = ['key' => 'value'];
        $this->assertSame('{"key":"value"}', Json::encode($data));
        $this->assertSame("{\n    \"key\": \"value\"\n}", Json::encode($data, JSON_PRETTY_PRINT));
    }

    public function test_encode_throws_on_invalid_data(): void
    {
        $this->expectException(JsonException::class);
        Json::encode(fopen('php://memory', 'r'));
    }

    /**
     * @throws JsonException
     */
    #[DataProvider('decodeProvider')]
    public function test_decode(string $json, mixed $expected): void
    {
        $this->assertSame($expected, Json::decode($json));
    }

    /**
     * @throws JsonException
     */
    public function test_decode_as_object(): void
    {
        $json   = '{"key":"value"}';
        $result = Json::decode($json, false);

        $this->assertIsObject($result);
        $this->assertSame('value', $result->key);
    }

    public function test_decode_throws_on_invalid_json(): void
    {
        $this->expectException(JsonException::class);
        Json::decode('invalid json');
    }

    /**
     * @throws JsonException
     */
    #[DataProvider('gzRoundtripProvider')]
    public function test_gz_encode_decode_roundtrip(mixed $data, mixed $expected): void
    {
        $encoded = Json::gzEncode($data);

        $this->assertIsString($encoded);
        $this->assertNotEmpty($encoded);
        $this->assertSame($expected, Json::gzDecode($encoded));
    }

    /**
     * @throws JsonException
     */
    public function test_gz_encode_is_base64(): void
    {
        $data    = ['test' => 'data'];
        $encoded = Json::gzEncode($data);

        $this->assertSame($encoded, base64_encode(base64_decode($encoded, true)));
    }

    /**
     * @throws JsonException
     */
    public function test_gz_encode_with_compression_level(): void
    {
        $data = str_repeat('compressible data ', 100);

        $lowCompression  = Json::gzEncode($data, 0, 512, 1);
        $highCompression = Json::gzEncode($data, 0, 512, 9);

        $this->assertSame($data, Json::gzDecode($lowCompression));
        $this->assertSame($data, Json::gzDecode($highCompression));
        $this->assertLessThan(strlen($lowCompression), strlen($highCompression));
    }

    public function test_gz_decode_throws_on_invalid_json(): void
    {
        $validGzippedInvalidJson = base64_encode(gzencode('not valid json'));

        $this->expectException(JsonException::class);
        Json::gzDecode($validGzippedInvalidJson);
    }

    public static function encodeProvider(): array
    {
        return [
            'string'        => ['hello', '"hello"'],
            'integer'       => [42, '42'],
            'float'         => [3.14, '3.14'],
            'boolean_true'  => [true, 'true'],
            'boolean_false' => [false, 'false'],
            'null'          => [null, 'null'],
            'array'         => [['a', 'b', 'c'], '["a","b","c"]'],
            'assoc_array'   => [['key' => 'value'], '{"key":"value"}'],
            'nested'        => [['outer' => ['inner' => 'value']], '{"outer":{"inner":"value"}}'],
            'empty_array'   => [[], '[]'],
            'empty_object'  => [new stdClass, '{}'],
        ];
    }

    public static function decodeProvider(): array
    {
        return [
            'string'        => ['"hello"', 'hello'],
            'integer'       => ['42', 42],
            'float'         => ['3.14', 3.14],
            'boolean_true'  => ['true', true],
            'boolean_false' => ['false', false],
            'null'          => ['null', null],
            'array'         => ['["a","b","c"]', ['a', 'b', 'c']],
            'assoc_array'   => ['{"key":"value"}', ['key' => 'value']],
            'nested'        => ['{"outer":{"inner":"value"}}', ['outer' => ['inner' => 'value']]],
            'empty_array'   => ['[]', []],
            'empty_object'  => ['{}', []],
        ];
    }

    public static function gzRoundtripProvider(): array
    {
        return [
            'string'        => ['hello', 'hello'],
            'integer'       => [42, 42],
            'float'         => [3.14, 3.14],
            'boolean_true'  => [true, true],
            'boolean_false' => [false, false],
            'null'          => [null, null],
            'array'         => [['a', 'b', 'c'], ['a', 'b', 'c']],
            'assoc_array'   => [['key' => 'value'], ['key' => 'value']],
            'nested'        => [['outer' => ['inner' => 'value']], ['outer' => ['inner' => 'value']]],
            'empty_array'   => [[], []],
        ];
    }
}
