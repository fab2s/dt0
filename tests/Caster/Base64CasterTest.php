<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace Tests\Caster;

use fab2s\Dt0\Caster\Base64Caster;
use fab2s\Dt0\Dt0;
use Tests\Artifacts\Base64TestDt0;
use Tests\TestCase;

class Base64CasterTest extends TestCase
{
    public function test_decode_on_input(): void
    {
        $caster  = Base64Caster::make();
        $encoded = base64_encode('hello world');

        // Input context: $data is array
        $result = $caster->cast($encoded, []);

        $this->assertSame('hello world', $result);
    }

    public function test_encode_on_output(): void
    {
        $caster = Base64Caster::make();
        $dt0    = $this->createMock(Dt0::class);

        // Output context: $data is Dt0
        $result = $caster->cast('hello world', $dt0);

        $this->assertSame(base64_encode('hello world'), $result);
    }

    public function test_null_passthrough(): void
    {
        $caster = Base64Caster::make();

        $this->assertNull($caster->cast(null, []));
        $this->assertNull($caster->cast(null, $this->createMock(Dt0::class)));
    }

    public function test_non_string_returns_null(): void
    {
        $caster = Base64Caster::make();

        $this->assertNull($caster->cast(123, []));
        $this->assertNull($caster->cast([], []));
        $this->assertNull($caster->cast(true, []));
    }

    public function test_invalid_base64_strict_returns_null(): void
    {
        $caster = new Base64Caster(strict: true);

        // Invalid base64 characters
        $this->assertNull($caster->cast('not valid base64!@#', []));
    }

    public function test_invalid_base64_non_strict_decodes(): void
    {
        $caster = new Base64Caster(strict: false);

        // Non-strict mode ignores invalid characters
        $result = $caster->cast('aGVsbG8=!!!', []);

        $this->assertSame('hello', $result);
    }

    public function test_binary_data_roundtrip(): void
    {
        $caster = Base64Caster::make();
        $binary = "\x00\x01\x02\xff\xfe\xfd";

        // Encode
        $encoded = $caster->cast($binary, $this->createMock(Dt0::class));

        // Decode
        $decoded = $caster->cast($encoded, []);

        $this->assertSame($binary, $decoded);
    }

    public function test_full_roundtrip_with_dto(): void
    {
        $input = ['data' => base64_encode('binary content')];

        $dt0 = Base64TestDt0::fromArray($input);

        // Input was decoded
        $this->assertSame('binary content', $dt0->data);

        // Output is encoded
        $output = $dt0->toJsonArray();
        $this->assertSame(base64_encode('binary content'), $output['data']);
    }
}
