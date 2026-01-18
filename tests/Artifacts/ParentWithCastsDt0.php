<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace Tests\Artifacts;

use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Attribute\Casts;
use fab2s\Dt0\Dt0;

#[Casts(
    inheritedCastProp: new Cast(default: 'inheritedDefault'),
)]
class ParentWithCastsDt0 extends Dt0
{
    public readonly string $inheritedCastProp;
}
