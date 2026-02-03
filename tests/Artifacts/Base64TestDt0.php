<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace Tests\Artifacts;

use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Caster\Base64Caster;
use fab2s\Dt0\Dt0;

class Base64TestDt0 extends Dt0
{
    #[Cast(in: Base64Caster::class, out: Base64Caster::class)]
    public readonly string $data;
}
