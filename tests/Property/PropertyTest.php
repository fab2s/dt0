<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace Tests\Property;

use fab2s\Dt0\Attribute\CastsInterface;
use fab2s\Dt0\Exception\Dt0Exception;
use fab2s\Dt0\Property\Properties;
use fab2s\Dt0\Validator\ValidatorInterface;
use ReflectionException;
use Tests\Artifacts\DefaultDt0;
use Tests\Artifacts\DummyValidatedDt0;
use Tests\Artifacts\NoOpValidator;
use Tests\TestCase;

class PropertyTest extends TestCase
{
    /**
     * @throws ReflectionException
     * @throws Dt0Exception
     */
    public function test_instance()
    {
        $properties = DefaultDt0::compile();
        $dto        = DefaultDt0::make(stringNoCast: 'stringNoCast', stringCast: 'stringCast');
        $this->assertInstanceOf(CastsInterface::class, $properties->casts);

        $expectedKeys = array_keys($dto->toArray());
        $this->assertSame($expectedKeys, array_keys($properties->toNames()));
        $this->assertSame($expectedKeys, array_keys($properties->toArray()));
    }

    public function test_validator()
    {
        $properties = new Properties(DummyValidatedDt0::class);

        $this->assertInstanceOf(ValidatorInterface::class, $properties->validator);

        /** @var NoOpValidator $validator */
        $validator = $properties->validator;
        $this->assertCount(3, $validator->rules);
        $this->assertSame('rule1', $validator->rules['fromValidate']->rule);
        $this->assertSame('rule2', $validator->rules['fromRules']->rule);
        $this->assertSame('rule3', $validator->rules['fromRule']->rule);
    }
}
