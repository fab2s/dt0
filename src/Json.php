<?php

declare(strict_types=1);

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
     * @param positive-int $depth
     *
     * @throws JsonException
     */
    public static function encode(mixed $data, int $flags = 0, int $depth = 512): string
    {
        return json_encode($data, $flags | JSON_THROW_ON_ERROR, $depth);
    }

    /**
     * @param positive-int $depth
     *
     * @throws JsonException
     */
    public static function gzEncode(mixed $data, int $flags = 0, int $depth = 512, int $level = 6): string
    {
        return base64_encode((string) gzencode(static::encode($data, $flags, $depth), $level));
    }

    /**
     * @param positive-int $depth
     *
     * @return ($associative is true ? array<string, mixed> : object)
     *
     * @throws JsonException
     */
    public static function decode(string $data, bool $associative = true, int $flags = 0, int $depth = 512): mixed
    {
        /** @var array<string, mixed>|object */
        return json_decode($data, $associative, $depth, $flags | JSON_THROW_ON_ERROR);
    }

    /**
     * @param positive-int $depth
     *
     * @return ($associative is true ? array<string, mixed> : object)
     *
     * @throws JsonException
     */
    public static function gzDecode(string $data, bool $associative = true, int $flags = 0, int $depth = 512): mixed
    {
        return static::decode((string) gzdecode(base64_decode($data)), $associative, $flags | JSON_THROW_ON_ERROR, $depth);
    }
}
