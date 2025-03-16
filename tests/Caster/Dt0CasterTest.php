<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace Tests\Caster;

use fab2s\Dt0\Caster\Dt0Caster;
use fab2s\Dt0\Exception\CasterException;
use fab2s\Dt0\Exception\Dt0Exception;
use Tests\TestCase;

class Dt0CasterTest extends TestCase
{
    /**
     * @throws Dt0Exception
     */
    public function test_exception(): void
    {
        $this->expectException(CasterException::class);
        Dt0Caster::make('NotAdt0');
    }
}
