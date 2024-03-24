<?php

/*
 * This file is part of fab2s/Dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/Dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Caster;

use fab2s\Dt0\Dt0;
use fab2s\Dt0\Exception\Dt0Exception;

class Dt0Caster implements CasterInterface
{
    /**
     * @throws Dt0Exception
     */
    public function __construct(
        public readonly string $dt0Fqn,
    ) {
        if (! is_subclass_of($dt0Fqn, Dt0::class)) {
            throw new Dt0Exception("$dt0Fqn does not extends " . Dt0::class);
        }
    }

    public function cast(mixed $value): ?Dt0
    {
        return $this->dt0Fqn::tryFrom($value);
    }
}
