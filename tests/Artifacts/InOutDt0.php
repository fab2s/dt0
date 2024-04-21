<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Tests\Artifacts;

use DateTimeImmutable;
use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Attribute\Casts;
use fab2s\Dt0\Caster\DateTimeCaster;
use fab2s\Dt0\Caster\DateTimeFormatCaster;
use fab2s\Dt0\Dt0;

#[Casts(
    new Cast(in: new DateTimeCaster, propName: 'classCastedIn'),
    classCastedInOut: new Cast(in: new DateTimeCaster, out: new DateTimeFormatCaster('Y-m-d H:i:s')),
    classCastedPromotedOut: new Cast(in: new DateTimeCaster, out: new DateTimeFormatCaster('Y-m-d H:i:s')),
)]
class InOutDt0 extends Dt0
{
    public readonly ?DateTimeImmutable $classCastedIn;
    public readonly ?DateTimeImmutable $classCastedInOut;

    #[Cast(in: new DateTimeCaster, out: new DateTimeFormatCaster('Y-m-d H:i:s'))]
    public readonly ?DateTimeImmutable $castedOut;

    public function __construct(
        public readonly ?DateTimeImmutable $classCastedPromotedOut,
        #[Cast(in: new DateTimeCaster, out: new DateTimeFormatCaster('Y-m-d H:i:s'))]
        public readonly ?DateTimeImmutable $castedPromotedInOut = null,
        ...$args,
    ) {
        parent::__construct(...$args);
    }
}
