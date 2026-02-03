<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace Tests\Caster;

use fab2s\Dt0\Caster\TrimCaster;
use fab2s\Dt0\Caster\TrimType;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TrimCasterTest extends TestCase
{
    #[DataProvider('trimProvider')]
    public function test_trim(TrimType $type, string $input, string $expected): void
    {
        $caster = TrimCaster::make($type);

        $this->assertSame($expected, $caster->cast($input));
    }

    public static function trimProvider(): array
    {
        return [
            'both sides' => [
                'type'     => TrimType::BOTH,
                'input'    => '  hello world  ',
                'expected' => 'hello world',
            ],
            'left only' => [
                'type'     => TrimType::LEFT,
                'input'    => '  hello world  ',
                'expected' => 'hello world  ',
            ],
            'right only' => [
                'type'     => TrimType::RIGHT,
                'input'    => '  hello world  ',
                'expected' => '  hello world',
            ],
            'tabs and newlines' => [
                'type'     => TrimType::BOTH,
                'input'    => "\t\nhello\r\n",
                'expected' => 'hello',
            ],
            'already trimmed' => [
                'type'     => TrimType::BOTH,
                'input'    => 'hello',
                'expected' => 'hello',
            ],
            'empty string' => [
                'type'     => TrimType::BOTH,
                'input'    => '   ',
                'expected' => '',
            ],
        ];
    }

    public function test_custom_characters(): void
    {
        $caster = new TrimCaster(TrimType::BOTH, 'xy');

        $this->assertSame('  hello  ', $caster->cast('xx  hello  yy'));
    }

    public function test_custom_characters_left(): void
    {
        $caster = new TrimCaster(TrimType::LEFT, '/');

        $this->assertSame('path/to/file/', $caster->cast('///path/to/file/'));
    }

    public function test_custom_characters_right(): void
    {
        $caster = new TrimCaster(TrimType::RIGHT, '/');

        $this->assertSame('///path/to/file', $caster->cast('///path/to/file/'));
    }

    public function test_null_on_non_string(): void
    {
        $caster = TrimCaster::make();

        $this->assertNull($caster->cast(null));
        $this->assertNull($caster->cast(123));
        $this->assertNull($caster->cast(12.34));
        $this->assertNull($caster->cast([]));
        $this->assertNull($caster->cast(true));
    }

    public function test_default_characters_constant(): void
    {
        $this->assertSame(" \n\r\t\v\0", TrimCaster::DEFAULT_CHARS);
    }
}
