<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Caster;

use fab2s\Dt0\Dt0;
use JsonException;

class JsonCaster extends CasterAbstract
{
    public function __construct(
        public readonly bool $associative = true,
        public readonly int $flags = 0,
        public readonly int $depth = 512,
    ) {}

    public static function make(
        bool $associative = true,
        int $flags = 0,
        int $depth = 512,
    ): static {
        return new static($associative, $flags, $depth);
    }

    /**
     * @throws JsonException
     */
    public function cast(mixed $value, array|Dt0|null $data = null): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($data instanceof Dt0) {
            // output
            return $this->encode($value);
        }

        if (is_string($value)) {
            return $this->decode($value);
        }

        return $value;
    }

    /**
     * @throws JsonException
     */
    protected function decode(string $value): array|object
    {
        return json_decode($value, $this->associative, $this->depth, $this->flags | JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonException
     */
    protected function encode(mixed $value): ?string
    {
        if (! is_array($value) && ! is_object($value)) {
            return null;
        }

        return json_encode($value, $this->flags | JSON_THROW_ON_ERROR, $this->depth);
    }
}
