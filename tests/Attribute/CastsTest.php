<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace Tests\Attribute;

use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Attribute\Casts;
use ReflectionClass;
use Tests\TestCase;

class CastsTest extends TestCase
{
    public function test_casts()
    {
        $casts = new Casts(
            new Cast(propName: 'prop1'),
            new Cast(default: 'casted'),
            prop2: new Cast(default: 'casted'),
        );

        $reflexion = new ReflectionClass($casts);

        $this->assertTrue($casts->hasCast('prop1'));
        $this->assertSame('prop1', $casts->getCast('prop1')->getPropName());
        $this->assertTrue($casts->hasCast('prop2'));
        $this->assertSame('prop2', $casts->getCast('prop2')->getPropName());
        $this->assertNull($reflexion->getProperty('declaringFqn')->getValue($casts));
        $this->assertCount(2, $reflexion->getProperty('casts')->getValue($casts));
    }
}
