<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Caster;

use fab2s\Enumerate\EnumerateInterface;
use fab2s\Enumerate\EnumerateTrait;

enum TrimType: string implements EnumerateInterface
{
    use EnumerateTrait;

    case BOTH  = 'trim';
    case LEFT  = 'ltrim';
    case RIGHT = 'rtrim';
}
