<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace Tests;

use Carbon\CarbonImmutable;
use fab2s\Dt0\Exception\Dt0Exception;
use fab2s\Dt0\Property\Properties;
use JsonException;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionException;
use Tests\Artifacts\DefaultDt0;
use Tests\Artifacts\Dt0Dt0;
use Tests\Artifacts\DummyDt0;
use Tests\Artifacts\DummyValidatedDt0;
use Tests\Artifacts\Enum\IntBackedEnum;
use Tests\Artifacts\Enum\StringBackedEnum;
use Tests\Artifacts\Enum\UnitEnum;
use Tests\Artifacts\EnumDt0;
use Tests\Artifacts\RenameDt0;
use Tests\Artifacts\SimpleDefaultDt0;
use TypeError;

class Dt0Test extends TestCase
{
    /**
     * @throws Dt0Exception
     * @throws JsonException
     * @throws ReflectionException
     */
    #[DataProvider('dt0Provider')]
    public function test_dto_dt0(string|EnumDt0|array $enumDt0, string|DefaultDt0|array|null $defaultDt0Default, string|DefaultDt0|array $defaultDt0): void
    {
        $dto = Dt0Dt0::fromArray(array_filter([
            'enumDt0'           => $enumDt0,
            'defaultDt0Default' => $defaultDt0Default,
            'defaultDt0'        => $defaultDt0,
        ]));

        $defaultDt0Default ??= ['stringNoCast' => 'assigned', 'stringCast' => 'assigned'];

        $this->assertSame([
            'enumDt0'           => EnumDt0::tryFrom($enumDt0)->toJsonArray(),
            'defaultDt0Default' => DefaultDt0::tryFrom($defaultDt0Default)->toJsonArray(),
            'defaultDt0'        => DefaultDt0::tryFrom($defaultDt0)->toJsonArray(),

        ], $dto->toJsonArray());

        $this->dt0Assertions($dto);
    }

    public function test_exception(): void
    {
        $this->expectException(Dt0Exception::class);
        new SimpleDefaultDt0;
    }

    /**
     * @throws ReflectionException
     */
    public function test_with_validation_exception(): void
    {
        $this->expectException(Dt0Exception::class);
        SimpleDefaultDt0::withValidation(...[]);
    }

    /**
     * @throws Dt0Exception
     * @throws ReflectionException
     */
    public function test_with_validation()
    {
        $dt0 = DummyValidatedDt0::withValidation(fromValidate: 'value1', fromRules: 'value2', fromRule: 'value3');

        $this->assertSame([
            'fromValidate' => 'value1',
            'fromRules'    => 'value2',
            'fromRule'     => 'value3',
        ], $dt0->toArray());
    }

    /**
     * @throws Dt0Exception
     * @throws ReflectionException
     */
    public function test_set_prop()
    {
        $dt0 = DummyDt0::make(readOnlyOne: 'value1', readOnlyTwo: 'value2', mutable: '2023-11-23');
        $this->assertEquals($dt0->mutable, CarbonImmutable::createFromDate(2023, 11, 23)->setTime(0, 0, 0));

        $dt0->setProp('mutable', '2025-11-23');
        $this->assertEquals($dt0->mutable, CarbonImmutable::createFromDate(2025, 11, 23)->setTime(0, 0, 0));

        $this->expectException(TypeError::class);
        $dt0->mutable = '2025-11-23';
    }

    /**
     * @throws Dt0Exception
     * @throws ReflectionException
     */
    public function test_update(): void
    {
        $dto     = DefaultDt0::make(stringNoCast: 'original', stringCast: 'someString');
        $updated = $dto->update(stringNoCast: 'updated');

        $this->assertSame('original', $dto->stringNoCast);
        $this->assertSame('updated', $updated->stringNoCast);
    }

    /**
     * @throws Dt0Exception
     * @throws ReflectionException
     * @throws JsonException
     */
    public function test_rename(): void
    {
        $dtos = [
            RenameDt0::make(input: 'value1', renamedTo: 'value2', inputCombo: 'value3'),
            RenameDt0::make(anotherInput: 'value1', renamedTo: 'value2', inputCombo: 'value3'),
        ];

        foreach ($dtos as $dto) {
            $this->assertSame('value1', $dto->renamedFrom);
            $this->assertSame('value2', $dto->renamedTo);
            $this->assertSame('value3', $dto->combo);

            $this->assertSame([
                'renamedFrom' => 'value1',
                'output'      => 'value2',
                'outputCombo' => 'value3',
            ], $dto->toArray());

            $this->assertSame([
                'renamedFrom' => 'value1',
                'output'      => 'value2',
                'outputCombo' => 'value3',
            ], $dto->toJsonArray());

            $this->dt0Assertions($dto);
        }
    }

    /**
     * @throws Dt0Exception
     * @throws ReflectionException
     */
    public function test_to_array_with_getter_on_public_property(): void
    {
        $dto = DummyDt0::make(readOnlyOne: 'value1', readOnlyTwo: 'value2', mutable: '2023-11-23');

        // Add a custom getter for a public property (covers lines 236-241 in Dt0::toArray)
        $dto->with('readOnlyOne', fn () => 'customValue');

        $result = $dto->toArray();

        // The custom getter should override the actual property value
        $this->assertSame('customValue', $result['readOnlyOne']);
        // Other properties should remain unchanged
        $this->assertSame('value2', $result['readOnlyTwo']);
    }

    /**
     * @throws Dt0Exception
     * @throws ReflectionException
     * @throws JsonException
     */
    public function test_to_gz(): void
    {
        $dto = DefaultDt0::make(stringNoCast: 'value1', stringCast: 'value2');

        $gz = $dto->toGz();

        $this->assertIsString($gz);
        $this->assertNotEmpty($gz);
        // Verify it's valid base64
        $this->assertSame($gz, base64_encode(base64_decode($gz, true)));
    }

    /**
     * @throws Dt0Exception
     * @throws ReflectionException
     * @throws JsonException
     */
    public function test_from_gz(): void
    {
        $original = DefaultDt0::make(stringNoCast: 'value1', stringCast: 'value2');

        $gz       = $original->toGz();
        $restored = DefaultDt0::fromGz($gz);

        $this->assertInstanceOf(DefaultDt0::class, $restored);
        $this->assertSame($original->toArray(), $restored->toArray());
        $this->assertSame($original->stringNoCast, $restored->stringNoCast);
        $this->assertSame($original->stringCast, $restored->stringCast);
    }

    /**
     * @throws Dt0Exception
     * @throws ReflectionException
     * @throws JsonException
     */
    public function test_gz_roundtrip(): void
    {
        $dto = EnumDt0::make(
            unitEnum: UnitEnum::ONE,
            stringBackedEnum: StringBackedEnum::ONE,
            intBackedEnum: IntBackedEnum::ONE,
        );

        $gz       = $dto->toGz();
        $restored = EnumDt0::fromGz($gz);

        $this->assertSame($dto->toArray(), $restored->toArray());
        $this->assertSame($dto->unitEnum, $restored->unitEnum);
        $this->assertSame($dto->stringBackedEnum, $restored->stringBackedEnum);
        $this->assertSame($dto->intBackedEnum, $restored->intBackedEnum);
    }

    /**
     * @throws Dt0Exception
     * @throws ReflectionException
     */
    public function test_get_properties(): void
    {
        $dto = DefaultDt0::make(stringNoCast: 'value1', stringCast: 'value2');

        $properties = $dto->getProperties();

        $this->assertInstanceOf(Properties::class, $properties);
        $this->assertSame(DefaultDt0::class, $properties->name);
        $this->assertArrayHasKey('stringNoCast', $properties->toArray());
        $this->assertArrayHasKey('stringCast', $properties->toArray());
    }

    /**
     * @throws Dt0Exception
     * @throws ReflectionException
     */
    public static function dt0Provider(): array
    {
        $defaultDt0 = DefaultDt0::make(stringNoCast: 'assigned', stringCast: 'assigned');
        $enumtDt0   = EnumDt0::make(unitEnum: UnitEnum::ONE, stringBackedEnum: StringBackedEnum::ONE, intBackedEnum: IntBackedEnum::ONE);

        return [
            'dt0' => [
                'enumDt0'           => $enumtDt0,
                'defaultDt0Default' => null,
                'defaultDt0'        => $defaultDt0,
            ],
            'string' => [
                'enumDt0'           => (string) $enumtDt0,
                'defaultDt0Default' => null,
                'defaultDt0'        => (string) $defaultDt0,
            ],
            'array' => [
                'enumDt0'           => $enumtDt0->toArray(),
                'defaultDt0Default' => null,
                'defaultDt0'        => $defaultDt0->toArray(),
            ],
        ];
    }
}
