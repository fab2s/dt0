<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Caster;

use fab2s\Dt0\Concern\HasDeclaringFqnInterface;
use fab2s\Dt0\Concern\HasPropNameInterface;
use fab2s\Dt0\Dt0;

interface CasterInterface extends HasDeclaringFqnInterface, HasPropNameInterface
{
    public function cast(mixed $value, array|Dt0|null $data = null): mixed;
}
