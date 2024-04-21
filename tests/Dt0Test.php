<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Tests;

use fab2s\Dt0\Tests\Artifacts\DefaultDt0;
use fab2s\Dt0\Tests\Artifacts\Dt0Dt0;
use fab2s\Dt0\Tests\Artifacts\Enum\IntBackedEnum;
use fab2s\Dt0\Tests\Artifacts\Enum\StringBackedEnum;
use fab2s\Dt0\Tests\Artifacts\Enum\UnitEnum;
use fab2s\Dt0\Tests\Artifacts\EnumDt0;
use JsonException;
use PHPUnit\Framework\Attributes\DataProvider;

class Dt0Test extends TestCase
{
    /**
     * @throws JsonException
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
