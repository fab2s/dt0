<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Tests\Attribute;

use fab2s\Dt0\Attribute\Validate;
use fab2s\Dt0\Exception\AttributeException;
use fab2s\Dt0\Tests\Artifacts\NoOpValidator;
use fab2s\Dt0\Tests\TestCase;
use fab2s\Dt0\Validator\ValidatorInterface;

class ValidateTest extends TestCase
{
    public function test_casts()
    {
        $validate = new Validate(NoOpValidator::class);

        $this->assertInstanceOf(ValidatorInterface::class, $validate->validator);
        $this->assertNull($validate->rules);

        $this->expectException(AttributeException::class);
        new Validate('NotAValidator');
    }
}
