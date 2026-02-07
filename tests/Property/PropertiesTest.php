<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace Tests\Property;

use fab2s\Dt0\Attribute\CastsInterface;
use fab2s\Dt0\Attribute\WithInterface;
use fab2s\Dt0\Exception\Dt0Exception;
use fab2s\Dt0\Property\Properties;
use fab2s\Dt0\Property\Property;
use fab2s\Dt0\Validator\ValidatorInterface;
use ReflectionException;
use Tests\Artifacts\ChildWithoutCastsDt0;
use Tests\Artifacts\DefaultDt0;
use Tests\Artifacts\DummyDt0;
use Tests\Artifacts\DummyValidatedDt0;
use Tests\Artifacts\DummyWithDt0;
use Tests\Artifacts\RenameDt0;
use Tests\Artifacts\SimpleDefaultDt0;
use Tests\TestCase;

class PropertiesTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function test_make(): void
    {
        $properties = Properties::make(DefaultDt0::class);

        $this->assertSame(DefaultDt0::class, $properties->name);
    }

    /**
     * @throws ReflectionException
     * @throws Dt0Exception
     */
    public function test_constructor_with_object(): void
    {
        $dto        = DefaultDt0::make(stringNoCast: 'value', stringCast: 'value');
        $properties = new Properties($dto);

        $this->assertSame(DefaultDt0::class, $properties->name);
    }

    /**
     * @throws ReflectionException
     */
    public function test_get(): void
    {
        $properties = Properties::make(DefaultDt0::class);

        $prop = $properties->get('stringNoCast');
        $this->assertInstanceOf(Property::class, $prop);
        $this->assertSame('stringNoCast', $prop->name);

        $this->assertNull($properties->get('nonExistentProp'));
    }

    /**
     * @throws ReflectionException
     */
    public function test_to_array(): void
    {
        $properties = Properties::make(DummyDt0::class);
        $array      = $properties->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('readOnlyOne', $array);
        $this->assertArrayHasKey('readOnlyTwo', $array);
        $this->assertArrayHasKey('mutable', $array);
        $this->assertContainsOnlyInstancesOf(Property::class, $array);
    }

    /**
     * @throws ReflectionException
     */
    public function test_to_names(): void
    {
        $properties = Properties::make(DummyDt0::class);
        $names      = $properties->toNames();

        $this->assertSame([
            'readOnlyOne' => 'readOnlyOne',
            'readOnlyTwo' => 'readOnlyTwo',
            'mutable'     => 'mutable',
        ], $names);

        // Test caching (calling again should return same result)
        $this->assertSame($names, $properties->toNames());
    }

    /**
     * @throws ReflectionException
     */
    public function test_early_inits(): void
    {
        $properties = Properties::make(DefaultDt0::class);
        $earlyInits = $properties->earlyInits();

        $this->assertIsArray($earlyInits);
        // Promoted properties with Cast attribute that have default or need casting
        $this->assertArrayHasKey('stringDefaultCastDefault', $earlyInits);
        $this->assertArrayHasKey('stringDefaultNullCastDefault', $earlyInits);
        $this->assertArrayHasKey('stringDefaultCastDefaultNull', $earlyInits);
        $this->assertContainsOnlyInstancesOf(Property::class, $earlyInits);
    }

    /**
     * @throws ReflectionException
     */
    public function test_constructor_parameters(): void
    {
        $properties = Properties::make(DefaultDt0::class);

        $this->assertIsArray($properties->constructorParameters);
        $this->assertArrayHasKey('stringNoCastDefault', $properties->constructorParameters);
        $this->assertArrayHasKey('stringDefaultCastDefault', $properties->constructorParameters);
    }

    /**
     * @throws ReflectionException
     */
    public function test_casts(): void
    {
        $properties = Properties::make(SimpleDefaultDt0::class);

        $this->assertInstanceOf(CastsInterface::class, $properties->casts);
        $this->assertTrue($properties->casts->hasCast('stringCast'));
        $this->assertTrue($properties->casts->hasCast('stringCastDefault'));
    }

    /**
     * @throws ReflectionException
     */
    public function test_with(): void
    {
        $properties = Properties::make(DummyWithDt0::class);

        $this->assertInstanceOf(WithInterface::class, $properties->with);
    }

    /**
     * @throws ReflectionException
     */
    public function test_validator(): void
    {
        $properties = Properties::make(DummyValidatedDt0::class);

        $this->assertInstanceOf(ValidatorInterface::class, $properties->validator);
    }

    /**
     * @throws ReflectionException
     */
    public function test_no_validator(): void
    {
        $properties = Properties::make(DummyDt0::class);

        $this->assertNull($properties->validator);
    }

    /**
     * @throws ReflectionException
     */
    public function test_rename_from_array(): void
    {
        $properties = Properties::make(RenameDt0::class);
        $renameFrom = $properties->getRenameFrom();

        // RenameDt0 has: #[Cast(renameFrom: ['input', 'anotherInput'])]
        $this->assertArrayHasKey('input', $renameFrom);
        $this->assertArrayHasKey('anotherInput', $renameFrom);
        $this->assertSame('renamedFrom', $renameFrom['input']);
        $this->assertSame('renamedFrom', $renameFrom['anotherInput']);
    }

    /**
     * @throws ReflectionException
     */
    public function test_rename_from_string(): void
    {
        $properties = Properties::make(RenameDt0::class);
        $renameFrom = $properties->getRenameFrom();

        // RenameDt0 has: #[Cast(renameFrom: 'inputCombo', renameTo: 'outputCombo')]
        $this->assertArrayHasKey('inputCombo', $renameFrom);
        $this->assertSame('combo', $renameFrom['inputCombo']);
    }

    /**
     * @throws ReflectionException
     */
    public function test_rename_to(): void
    {
        $properties = Properties::make(RenameDt0::class);
        $renameFrom = $properties->getRenameFrom();

        // RenameDt0 has: #[Cast(renameTo: 'output')]
        // renameTo also adds to renameFrom
        $this->assertArrayHasKey('output', $renameFrom);
        $this->assertSame('renamedTo', $renameFrom['output']);
    }

    /**
     * @throws ReflectionException
     */
    public function test_get_to_name(): void
    {
        $properties = Properties::make(RenameDt0::class);

        // Property with renameTo
        $this->assertSame('output', $properties->getToName('renamedTo'));
        $this->assertSame('outputCombo', $properties->getToName('combo'));

        // Property without renameTo returns original name
        $this->assertSame('renamedFrom', $properties->getToName('renamedFrom'));
        $this->assertSame('nonExistent', $properties->getToName('nonExistent'));
    }

    /**
     * @throws ReflectionException
     */
    public function test_no_casts(): void
    {
        $properties = Properties::make(DummyDt0::class);

        // DummyDt0 has no class-level Casts attribute (only property-level Cast)
        $this->assertNull($properties->casts);
    }

    /**
     * @throws ReflectionException
     */
    public function test_no_with(): void
    {
        $properties = Properties::make(DummyDt0::class);

        $this->assertNull($properties->with);
    }

    /**
     * @throws ReflectionException
     */
    public function test_property_default_from_constructor_parameter(): void
    {
        $properties = Properties::make(DefaultDt0::class);

        // stringNoCastDefault has default value 'default' in constructor
        $prop = $properties->get('stringNoCastDefault');
        $this->assertTrue($prop->hasDefault());
        $this->assertSame('default', $prop->getDefault());
    }

    /**
     * @throws ReflectionException
     */
    public function test_casts_inherited_from_parent(): void
    {
        $properties = Properties::make(ChildWithoutCastsDt0::class);

        // ChildWithoutCastsDt0 has no Casts attribute, but ParentWithCastsDt0 does
        $this->assertInstanceOf(CastsInterface::class, $properties->casts);
        $this->assertTrue($properties->casts->hasCast('inheritedCastProp'));
    }
}
