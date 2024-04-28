<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Tests\Property;

use fab2s\Dt0\Exception\Dt0Exception;
use fab2s\Dt0\Property\Property;
use fab2s\Dt0\Tests\Artifacts\Enum\IntBackedEnum;
use fab2s\Dt0\Tests\Artifacts\Enum\StringBackedEnum;
use fab2s\Dt0\Tests\Artifacts\Enum\UnitEnum;
use fab2s\Dt0\Tests\TestCase;

class PropertyTest extends TestCase
{
    public function test_try_enum_from()
    {
        $this->assertSame(null, Property::tryEnumFrom(null, null));
        $this->assertSame(StringBackedEnum::ONE, Property::tryEnumFrom(StringBackedEnum::class, 'ONE'));
        $this->assertSame(IntBackedEnum::ONE, Property::tryEnumFrom(IntBackedEnum::class, 1));
        $this->assertSame(null, Property::tryEnumFromName(UnitEnum::class, 'notACase'));
        $this->assertSame(UnitEnum::ONE, Property::tryEnumFromName(UnitEnum::class, 'ONE'));
    }

    public function test_enum_from()
    {
        $this->assertSame(StringBackedEnum::ONE, Property::enumFrom(StringBackedEnum::class, 'ONE'));
        $this->assertSame(UnitEnum::ONE, Property::enumFrom(UnitEnum::class, 'ONE'));

        $this->expectException(Dt0Exception::class);
        $this->assertSame(null, Property::enumFrom(UnitEnum::class, 'notACase'));
    }
}
