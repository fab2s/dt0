<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Caster;

use fab2s\Math\Math;

class MathCaster implements CasterInterface
{
    public readonly int $precision;

    public function __construct(
        int $precision = Math::PRECISION,
    ) {
        $this->precision = max(0, $precision);
    }

    public function cast(mixed $value): ?Math
    {
        return Math::isNumber($value) ? Math::number($value)->setPrecision($this->precision) : null;
    }
}
