<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Tests\Artifacts;

use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Caster\Dt0Caster;
use fab2s\Dt0\Dt0;

class Dt0Dt0 extends Dt0
{
    public readonly EnumDt0 $enumDt0;

    #[Cast(in: new Dt0Caster(dt0Fqn: DefaultDt0::class), default: ['stringNoCast' => 'assigned', 'stringCast' => 'assigned'])]
    public readonly DefaultDt0 $defaultDt0Default;

    public function __construct(
        public readonly DefaultDt0 $defaultDt0,
        ...$args,
    ) {
        parent::__construct(...$args);
    }
}
