<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace Tests\Property;

use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Attribute\CastInterface;
use fab2s\Dt0\Attribute\CastsInterface;
use fab2s\Dt0\Exception\Dt0Exception;
use fab2s\Dt0\Property\Properties;
use fab2s\Dt0\Property\Property;
use fab2s\Dt0\Validator\ValidatorInterface;
use ReflectionClass;
use ReflectionException;
use Tests\Artifacts\ChildDt0;
use Tests\Artifacts\DefaultDt0;
use Tests\Artifacts\DummyValidatedDt0;
use Tests\Artifacts\GreatGrandchildDt0;
use Tests\Artifacts\MiddleDt0;
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

    /**
     * @throws ReflectionException
     */
    public function test_resolve_attribute_direct(): void
    {
        $reflection = new ReflectionClass(DefaultDt0::class);
        $property   = $reflection->getProperty('stringCastDefaultNull');

        $attribute = Property::resolveAttribute($property, CastInterface::class);

        $this->assertInstanceOf(Cast::class, $attribute);
        $this->assertNull($attribute->default);
    }

    /**
     * @throws ReflectionException
     */
    public function test_resolve_attribute_returns_null_when_not_found(): void
    {
        $reflection = new ReflectionClass(DefaultDt0::class);
        $property   = $reflection->getProperty('stringNoCast');

        $attribute = Property::resolveAttribute($property, CastInterface::class);

        $this->assertNull($attribute);
    }

    /**
     * @throws ReflectionException
     */
    public function test_resolve_attribute_from_parent(): void
    {
        $reflection = new ReflectionClass(ChildDt0::class);
        $property   = $reflection->getProperty('inheritedProp');

        $attribute = Property::resolveAttribute($property, CastInterface::class);

        $this->assertInstanceOf(Cast::class, $attribute);
        $this->assertSame('parentDefault', $attribute->default);
    }

    /**
     * @throws ReflectionException
     */
    public function test_resolve_attribute_child_property(): void
    {
        $reflection = new ReflectionClass(ChildDt0::class);
        $property   = $reflection->getProperty('childProp');

        $attribute = Property::resolveAttribute($property, CastInterface::class);

        $this->assertInstanceOf(Cast::class, $attribute);
        $this->assertSame('childDefault', $attribute->default);
    }

    /**
     * @throws ReflectionException
     */
    public function test_resolve_attribute_walks_parent_chain(): void
    {
        $reflection = new ReflectionClass(MiddleDt0::class);
        $property   = $reflection->getProperty('deepInheritedProp');

        // Property is redeclared in MiddleDt0 via constructor promotion (no attribute)
        // resolveAttribute should walk up to GrandparentDt0 to find the Cast attribute
        $this->assertSame(MiddleDt0::class, $property->getDeclaringClass()->getName());

        $attribute = Property::resolveAttribute($property, CastInterface::class);

        $this->assertInstanceOf(Cast::class, $attribute);
        $this->assertSame('grandparentDefault', $attribute->default);
    }

    /**
     * @throws ReflectionException
     */
    public function test_resolve_attribute_skips_parent_without_attribute(): void
    {
        $reflection = new ReflectionClass(GreatGrandchildDt0::class);
        $property   = $reflection->getProperty('deepInheritedProp');

        // Chain: GreatGrandchildDt0 -> IntermediateDt0 -> GrandparentDt0
        // GreatGrandchildDt0: redeclares property (no attribute)
        // IntermediateDt0: has property but no attribute (triggers ?? null fallback)
        // GrandparentDt0: has property WITH attribute
        $this->assertSame(GreatGrandchildDt0::class, $property->getDeclaringClass()->getName());

        $attribute = Property::resolveAttribute($property, CastInterface::class);

        $this->assertInstanceOf(Cast::class, $attribute);
        $this->assertSame('grandparentDefault', $attribute->default);
    }
}
