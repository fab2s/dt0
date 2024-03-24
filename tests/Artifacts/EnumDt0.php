<?php

/*
 * This file is part of fab2s/Dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/Dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Tests\Artifacts;

use fab2s\Dt0\Dt0;
use fab2s\Dt0\Tests\Artifacts\Enum\IntBackedEnum;
use fab2s\Dt0\Tests\Artifacts\Enum\StringBackedEnum;
use fab2s\Dt0\Tests\Artifacts\Enum\UnitEnum;

class EnumDt0 extends Dt0
{
    public readonly UnitEnum $unitEnum;
    public readonly StringBackedEnum $stringBackedEnum;
    public readonly IntBackedEnum $intBackedEnum;

    public function __construct(
        public readonly UnitEnum $unitEnumWithDefault = UnitEnum::ONE,
        public readonly StringBackedEnum $stringBackedEnumWithDefault = StringBackedEnum::ONE,
        public readonly IntBackedEnum $intBackedEnumWithDefault = IntBackedEnum::ONE,
        ...$args,
    ) {
        parent::__construct(...$args);
    }
}
