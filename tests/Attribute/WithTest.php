<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace Tests\Attribute;

use fab2s\Dt0\Attribute\With;
use fab2s\Dt0\Attribute\WithProp;
use fab2s\Dt0\Exception\AttributeException;
use ReflectionClass;
use Tests\TestCase;

class WithTest extends TestCase
{
    /**
     * @throws AttributeException
     */
    public function test_with()
    {
        $with = new With(
            new WithProp(name: 'prop1'),
            new WithProp,
            prop2: WithProp::make(),
        );

        $reflexion = new ReflectionClass($with);

        $this->assertTrue($with->hasWith('prop1'));
        $this->assertSame('prop1', $with->getWith('prop1')->getPropName());
        $this->assertTrue($with->hasWith('prop2'));
        $this->assertSame('prop2', $with->getWith('prop2')->getPropName());
        $this->assertNull($reflexion->getProperty('declaringFqn')->getValue($with));

        $this->assertCount(2, $with->getWiths());
    }
}
