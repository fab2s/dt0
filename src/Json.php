<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0;

use JsonException;

class Json
{
    /**
     * @throws JsonException
     */
    public static function encode(mixed $data, int $flags = 0, int $depth = 512): false|string
    {
        return json_encode($data, $flags & JSON_THROW_ON_ERROR, $depth);
    }

    /**
     * @throws JsonException
     */
    public static function gzEncode(mixed $data, int $flags = 0, int $depth = 512, int $level = 6): string
    {
        return base64_encode(gzencode(static::encode($data, $flags, $depth), $level));
    }

    /**
     * @throws JsonException
     */
    public static function decode(string $data, bool $associative = true, int $flags = 0, int $depth = 512): false|array
    {
        return json_decode($data, $associative, $depth, $flags & JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonException
     */
    public static function gzDecode(string $data, bool $associative = true, int $flags = 0, int $depth = 512): false|array
    {
        return static::decode(gzdecode(base64_decode($data)), $associative, $flags, $depth);
    }
}
