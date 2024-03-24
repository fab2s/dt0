<?php

/*
 * This file is part of fab2s/Dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/Dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Tests\Artifacts;

use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Attribute\Casts;
use fab2s\Dt0\Dt0;

#[Casts(
    new Cast(default: 'default', propName: 'stringCast'),
    stringCastDefault: new Cast(default: 'default'),
)]
class SimpleDefaultDt0 extends Dt0
{
    public readonly string $stringNoCast;
    public readonly string $stringCast;
    public readonly string $stringCastDefault;
}
