<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace Tests\Caster;

use fab2s\Dt0\Caster\ClassCaster;
use stdClass;
use Tests\Artifacts\SimpleClass;
use Tests\TestCase;
use TypeError;

class ClassCasterTest extends TestCase
{
    public function test_make(): void
    {
        $caster = ClassCaster::make(SimpleClass::class, 'value1', 'value2');

        $this->assertSame(SimpleClass::class, $caster->fqn);
        $this->assertSame(['value1', 'value2'], $caster->parameters);
    }

    public function test_constructor(): void
    {
        $caster = new ClassCaster(SimpleClass::class, 'param1', 'param2');

        $this->assertSame(SimpleClass::class, $caster->fqn);
        $this->assertSame(['param1', 'param2'], $caster->parameters);
    }

    public function test_cast_instance(): void
    {
        $caster   = ClassCaster::make(SimpleClass::class);
        $instance = new SimpleClass('existing');

        $result = $caster->cast($instance);

        $this->assertSame($instance, $result);
    }

    public function test_cast_array(): void
    {
        $caster = ClassCaster::make(SimpleClass::class);

        $result = $caster->cast(['value' => 'fromArray', 'extra' => 'extraValue']);

        $this->assertInstanceOf(SimpleClass::class, $result);
        $this->assertSame('fromArray', $result->value);
        $this->assertSame('extraValue', $result->extra);
    }

    public function test_cast_array_positional(): void
    {
        $caster = ClassCaster::make(SimpleClass::class);

        $result = $caster->cast(['positional', 'extraPositional']);

        $this->assertInstanceOf(SimpleClass::class, $result);
        $this->assertSame('positional', $result->value);
        $this->assertSame('extraPositional', $result->extra);
    }

    public function test_cast_scalar(): void
    {
        $caster = ClassCaster::make(SimpleClass::class);

        $result = $caster->cast('scalarValue');

        $this->assertInstanceOf(SimpleClass::class, $result);
        $this->assertSame('scalarValue', $result->value);
        $this->assertSame('default', $result->extra);
    }

    public function test_cast_scalar_type_error(): void
    {
        $caster = ClassCaster::make(SimpleClass::class);

        $this->expectException(TypeError::class);
        $caster->cast(42);
    }

    public function test_cast_default_parameters(): void
    {
        $caster = ClassCaster::make(SimpleClass::class, 'defaultValue', 'defaultExtra');

        // Non-scalar, non-array, non-instance value triggers default branch
        $result = $caster->cast(null);

        $this->assertInstanceOf(SimpleClass::class, $result);
        $this->assertSame('defaultValue', $result->value);
        $this->assertSame('defaultExtra', $result->extra);
    }

    public function test_cast_default_with_object(): void
    {
        $caster      = ClassCaster::make(SimpleClass::class, 'fallback', 'fallbackExtra');
        $otherObject = new stdClass;

        // Object that is not instance of fqn triggers default branch
        $result = $caster->cast($otherObject);

        $this->assertInstanceOf(SimpleClass::class, $result);
        $this->assertSame('fallback', $result->value);
        $this->assertSame('fallbackExtra', $result->extra);
    }
}
