<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Attribute;

use fab2s\Dt0\Concern\HasDeclaringFqn;
use fab2s\Dt0\Concern\HasPropName;

abstract class WithPropAbstract implements WithPropInterface
{
    use HasDeclaringFqn;
    use HasPropName;
    public readonly ?string $name;
    public readonly string|bool|null $getter;
}
