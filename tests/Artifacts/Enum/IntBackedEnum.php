<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace Tests\Artifacts\Enum;

enum IntBackedEnum: int
{
    case ONE   = 1;
    case TWO   = 2;
    case three = 3;
}
