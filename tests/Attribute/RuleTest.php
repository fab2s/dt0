<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Tests\Attribute;

use fab2s\Dt0\Attribute\Rule;
use fab2s\Dt0\Tests\TestCase;

class RuleTest extends TestCase
{
    public function test_casts()
    {
        $rule = new Rule('rule');

        $this->assertSame('rule', $rule->rule);
        $this->assertNull($rule->propName);
    }
}
