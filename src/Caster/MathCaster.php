<?php

declare(strict_types=1);

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
    public readonly ?Math $default;

    public function __construct(
        int $precision = Math::PRECISION,
        int|float|string|Math|null $default = null,
    ) {
        $this->precision = max(0, $precision);
        /** @var int|float|string|Math $default */
        $this->default = Math::isNumber($default) ? Math::number($default)->setPrecision($this->precision) : null;
    }

    public static function make(
        int $precision = Math::PRECISION,
        int|float|string|Math|null $default = null,
    ): static {
        return new static($precision, $default);
    }

    /** @param array<string, mixed>|Dt0|null $data */
    public function cast(mixed $value, array|Dt0|null $data = null): ?Math
    {
        /** @var int|float|string|Math $value */
        return Math::isNumber(trim((string) $value)) ? Math::number($value)->setPrecision($this->precision) : $this->default;
    }
}
