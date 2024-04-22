<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Caster;

use fab2s\Dt0\Dt0;
use fab2s\Dt0\Exception\CasterException;

class ScalarTypeCaster implements CasterInterface
{
    public readonly ?ScalarType $type;

    public function __construct(
        ScalarType|string $type,
    ) {
        if (is_string($type) && ! ($type = ScalarType::tryFrom($type))) {
            throw new CasterException('[' . Dt0::classBasename(static::class) . "] $type is not ScalarType");
        }

        $this->type = $type;

    }

    /**
     * @param scalar $value
     *
     * @return string|int|float|bool|null|resource
     */
    public function cast(mixed $value): mixed
    {
        if (! is_scalar($value)) {
            return null;
        }

        return match ($this->type) {
            ScalarType::resource => is_resource($value) ? $value : null,
            default              => settype($value, $this->type?->value) ? $value : null,
        };
    }
}
