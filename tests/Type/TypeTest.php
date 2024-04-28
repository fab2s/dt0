<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Tests\Type;

use DateTime;
use DateTimeImmutable;
use fab2s\Dt0\Property\Properties;
use fab2s\Dt0\Tests\Artifacts\TypedDt0;
use fab2s\Dt0\Tests\TestCase;

class TypeTest extends TestCase
{
    public function test_types()
    {
        $properties    = new Properties(TypedDt0::class);
        $unionTypeProp = $properties->get('unionType');
        $this->assertFalse($unionTypeProp->hasDefault());

        $unionTypePropTypes = $unionTypeProp->types;
        $this->assertCount(2, $unionTypePropTypes->toArray());
        $this->assertTrue($unionTypePropTypes->isUnion);
        $this->assertTrue($unionTypePropTypes->isReadOnly);
        $this->assertFalse($unionTypePropTypes->isNullable);
        $this->assertFalse($unionTypePropTypes->isIntersection);

        $this->assertTrue($unionTypePropTypes->has(DateTimeImmutable::class));
        $this->assertTrue($unionTypePropTypes->has(DateTime::class));

        $dateTimeType = $unionTypePropTypes->get(DateTime::class);
        $this->assertTrue($dateTimeType->isUnion);
        $this->assertFalse($dateTimeType->isBuiltin);
        $this->assertFalse($dateTimeType->allowsNull);
        $this->assertFalse($dateTimeType->isIntersection);

        $dateTimeImmutableType = $unionTypePropTypes->get(DateTimeImmutable::class);
        $this->assertTrue($dateTimeImmutableType->isUnion);
        $this->assertFalse($dateTimeImmutableType->isBuiltin);
        $this->assertFalse($dateTimeImmutableType->allowsNull);
        $this->assertFalse($dateTimeImmutableType->isIntersection);

        $unionTypeNullableProp = $properties->get('unionTypeNullable');
        $this->assertFalse($unionTypeNullableProp->hasDefault());

        $unionTypePropNullableTypes = $unionTypeNullableProp->types;
        $this->assertCount(2, $unionTypePropTypes->toArray());
        $this->assertTrue($unionTypePropNullableTypes->isUnion);
        $this->assertTrue($unionTypePropNullableTypes->isReadOnly);
        $this->assertTrue($unionTypePropNullableTypes->isNullable);
        $this->assertFalse($unionTypePropNullableTypes->isIntersection);

        $this->assertTrue($unionTypePropNullableTypes->has(DateTimeImmutable::class));
        $this->assertTrue($unionTypePropNullableTypes->has(DateTime::class));
        $this->assertTrue($unionTypePropNullableTypes->has('null'));

        $nullType = $unionTypePropTypes->get('null');
        $this->assertNull($nullType);

        $unTypedProp = $properties->get('unTyped');
        $this->assertTrue($unTypedProp->hasDefault());

        $unTypedPropTypes = $unTypedProp->types;
        $this->assertCount(1, $unTypedPropTypes->toArray());

        $this->assertFalse($unTypedPropTypes->isUnion);
        $this->assertFalse($unTypedPropTypes->isReadOnly);
        $this->assertTrue($unTypedPropTypes->isNullable);
        $this->assertFalse($unTypedPropTypes->isIntersection);

        $intersectionTypeProp = $properties->get('intersectionType');
        $this->assertFalse($intersectionTypeProp->hasDefault());

        $intersectionTypePropTypes = $intersectionTypeProp->types;
        $this->assertCount(2, $intersectionTypePropTypes->toArray());
        $this->assertFalse($intersectionTypePropTypes->isUnion);
        $this->assertTrue($intersectionTypePropTypes->isReadOnly);
        $this->assertFalse($intersectionTypePropTypes->isNullable);
        $this->assertTrue($intersectionTypePropTypes->isIntersection);

        $this->assertTrue($intersectionTypePropTypes->has(DateTimeImmutable::class));
        $this->assertTrue($intersectionTypePropTypes->has(DateTime::class));
    }
}
