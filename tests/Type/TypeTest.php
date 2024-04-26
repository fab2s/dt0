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
use fab2s\Dt0\Tests\Artifacts\TypedDt0;
use fab2s\Dt0\Tests\TestCase;

class TypeTest extends TestCase
{
    public function test_try_enum()
    {
        $dto = TypedDt0::make(unionType: new DateTimeImmutable('now'), unionTypeNullable: null, unTyped: 'whatever');

        $properties    = $dto->getDt0Properties();
        $unionTypeProp = $properties->get('unionType');
        $this->assertFalse($unionTypeProp->hasDefault());
        $unionTypePropTypes = $unionTypeProp->types;
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
        $this->assertFalse($unTypedPropTypes->isUnion);
        $this->assertFalse($unTypedPropTypes->isReadOnly);
        $this->assertTrue($unTypedPropTypes->isNullable);
        $this->assertFalse($unTypedPropTypes->isIntersection);
    }
}