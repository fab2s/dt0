<?php

/*
 * This file is part of fab2s/Dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/Dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Caster;

use fab2s\Math\Math;
use InvalidArgumentException;

class MathCaster implements CasterInterface
{
    public readonly ?Math $number;
    public readonly int $precision;

    public function __construct(
        int $precision = Math::PRECISION,
        public readonly bool $nullable = false,
    ) {
        $this->precision = max(0, $precision);
    }

    public function cast(mixed $value): ?Math
    {
        if ($this->nullable && $value === null) {
            return null;
        }

        $isNumber = Math::isNumber($value);
        if (! $this->nullable && ! $isNumber) {
            throw new InvalidArgumentException('Value is not a number');
        }

        return $isNumber ? Math::number($value) : null;
    }
}
