<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Caster;

use fab2s\Dt0\Dt0;
use fab2s\Math\Math;

class MathCaster extends CasterAbstract
{
    public readonly int $precision;

    public function __construct(
        int $precision = Math::PRECISION,
    ) {
        $this->precision = max(0, $precision);
    }

    public static function make(
        int $precision = Math::PRECISION,
    ): static {
        return new static($precision);
    }

    public function cast(mixed $value, array|Dt0|null $data = null): Math
    {
        return Math::number($value)->setPrecision($this->precision);
    }
}
