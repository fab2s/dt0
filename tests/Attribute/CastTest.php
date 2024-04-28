<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Tests\Attribute;

use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Exception\AttributeException;
use fab2s\Dt0\Tests\TestCase;

class CastTest extends TestCase
{
    public function test_casts()
    {
        $cast = new Cast;

        $this->assertNull($cast->in);
        $this->assertNull($cast->out);
        $this->assertSame(Dt0::DT0_NIL, $cast->default);
        $this->assertNull($cast->renameFrom);
        $this->assertNull($cast->renameTo);
        $this->assertNull($cast->propName);
        $this->assertFalse($cast->hasDefault);

        $this->expectException(AttributeException::class);
        new Cast(in: 'NotACaster');
    }
}
