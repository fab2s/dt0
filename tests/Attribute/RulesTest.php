<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Tests\Attribute;

use fab2s\Dt0\Attribute\Rule;
use fab2s\Dt0\Attribute\Rules;
use fab2s\Dt0\Tests\TestCase;
use ReflectionClass;

class RulesTest extends TestCase
{
    public function test_casts()
    {
        $rules = new Rules(
            new Rule(rule: 'rule', propName: 'prop1'),
            new Rule(rule: 'rule'),
            prop2: new Rule(rule: 'rule'),
        );

        $reflexion = new ReflectionClass($rules);

        $this->assertTrue($rules->hasRule('prop1'));
        $this->assertSame('rule', $rules->getRule('prop1')->rule);
        $this->assertTrue($rules->hasRule('prop2'));
        $this->assertSame('rule', $rules->getRule('prop2')->rule);

        $this->assertCount(2, $reflexion->getProperty('rules')->getValue($rules));
    }
}
