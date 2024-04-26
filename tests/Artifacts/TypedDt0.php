<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Tests\Artifacts;

use DateTime;
use DateTimeImmutable;
use fab2s\Dt0\Dt0;

class TypedDt0 extends Dt0
{
    public readonly DateTime|DateTimeImmutable $unionType;
    public readonly DateTime|DateTimeImmutable|null $unionTypeNullable;
    public $unTyped = false;
}
