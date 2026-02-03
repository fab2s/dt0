<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Caster;

use fab2s\Dt0\Dt0;

class Base64Caster extends CasterAbstract
{
    public function __construct(
        public readonly bool $strict = true,
    ) {}

    public static function make(
        bool $strict = true,
    ): static {
        return new static($strict);
    }

    /**
     * On input ($data is array): decodes base64 string.
     * On output ($data is Dt0): encodes to base64 string.
     */
    public function cast(mixed $value, array|Dt0|null $data = null): ?string
    {
        if ($value === null || ! is_string($value)) {
            return null;
        }

        // Output context: encode to base64
        if ($data instanceof Dt0) {
            return base64_encode($value);
        }

        // Input context: decode from base64
        $decoded = base64_decode($value, $this->strict);

        return $decoded === false ? null : $decoded;
    }
}
