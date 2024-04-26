<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Tests\Property;

use fab2s\Dt0\Property\Property;
use fab2s\Dt0\Tests\Artifacts\Enum\StringBackedEnum;
use fab2s\Dt0\Tests\Artifacts\Enum\UnitEnum;
use fab2s\Dt0\Tests\TestCase;

class PropertyTest extends TestCase
{
    public function test_try_enum()
    {
        $this->assertSame(null, Property::tryEnum(null, null));
        $this->assertSame(null, Property::tryEnum(StringBackedEnum::class, null));
        $this->assertSame(null, Property::tryEnumFromName(UnitEnum::class, 'notACase'));
    }
}
