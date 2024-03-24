<?php

/*
 * This file is part of fab2s/Dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/Dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Type;

class Type
{
    public function __construct(
        public readonly string $name,
        public readonly bool $allowsNull = false,
        public readonly bool $isBuiltin = false,
        public readonly bool $isUnion = false,
        public readonly bool $isIntersection = false,
    ) {
    }

    public static function make(
        string $name,
        bool $allowsNull = false,
        bool $isBuiltin = false,
        bool $isUnion = false,
        bool $isIntersection = false,
    ): static {
        return new static($name, $allowsNull, $isBuiltin, $isUnion, $isIntersection);
    }
}
