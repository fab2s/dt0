<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace Tests\Caster;

use DateInvalidTimeZoneException;
use Exception;
use fab2s\Dt0\Caster\ArrayOfCaster;
use fab2s\Dt0\Caster\CarbonCaster;
use fab2s\Dt0\Caster\CasterCollection;
use fab2s\Dt0\Caster\CasterCollectionAbstract;
use fab2s\Dt0\Caster\CasterInterface;
use fab2s\Dt0\Caster\DateTimeCaster;
use fab2s\Dt0\Caster\DateTimeFormatCaster;
use fab2s\Dt0\Caster\Dt0Caster;
use fab2s\Dt0\Caster\MathCaster;
use fab2s\Dt0\Caster\ScalarCaster;
use fab2s\Dt0\Caster\ScalarType;
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Exception\CasterException;
use fab2s\Dt0\Exception\Dt0Exception;
use fab2s\Math\Math;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Artifacts\DummyDt0;
use Tests\TestCase;

#[CoversClass(CasterCollectionAbstract::class)]
class CasterCollectionTest extends TestCase
{
    #[DataProvider('castProvider')]
    public function test_cast(array $casters, $input, $expected): void
    {
        $casterCollection = CasterCollection::make(...$casters);

        $this->assertInstanceOf(CasterCollection::class, $casterCollection);
        $this->assertFalse($casterCollection->isEmpty());
        $this->assertCount(count($casters), $casterCollection);
        $this->assertSame(count($casters), $casterCollection->count());

        $propName     = 'PropName';
        $declaringFqn = 'DeclaringFqn';
        $casterCollection->setPropName($propName)
            ->setDeclaringFqn($declaringFqn)
        ;

        $this->assertSame($expected, $this->parseResult($casterCollection->cast($input)));

        $caster = $casterCollection->toCaster();
        $this->assertInstanceOf(CasterInterface::class, $caster);
        $this->assertSame($expected, $this->parseResult($caster->cast($input)));

        $cnt = 0;
        foreach ($casterCollection as $caster) {
            $this->assertInstanceOf(CasterInterface::class, $caster);
            $this->assertSame($casters[$cnt++], $casterCollection->get($caster));
            $this->assertTrue($casterCollection->has(get_class($caster)));
            $this->assertSame($caster, $casterCollection->get($caster));
            $this->assertSame($propName, $caster->getPropName());
            $this->assertSame($declaringFqn, $caster->getDeclaringFqn());

            $casterCollection->delete($caster);
            $this->assertFalse($casterCollection->has(get_class($caster)));

            $casterCollection->push($caster);
            $this->assertTrue($casterCollection->has(get_class($caster)));
            $this->assertSame($caster, $casterCollection->get($caster));
        }
    }

    public function test_strings(): void
    {
        $casterCollection = CasterCollection::make(new ScalarCaster(ScalarType::string), MathCaster::class);
        $caster           = $casterCollection->toCaster();
        $result           = $caster->cast(42);
        $this->assertInstanceOf(Math::class, $result);
        $this->assertTrue(Math::make('42')->eq($caster->cast(42)));

        $this->expectException(CasterException::class);
        CasterCollection::make('SurelyNotACasterInterfaceClassString');

    }

    protected function parseResult($result)
    {
        return match (true) {
            $result instanceof Dt0  => $result->toJsonArray(),
            $result instanceof Math => (string) $result,
            default                 => $result,
        };
    }

    /**
     * @throws DateInvalidTimeZoneException
     * @throws Dt0Exception
     * @throws CasterException
     * @throws Exception
     */
    public static function castProvider(): array
    {
        return [
            'string_chain' => [
                'casters' => [
                    CarbonCaster::make(),
                    DateTimeCaster::make(),
                    DateTimeFormatCaster::make('Y-m-d H:i:s'),
                    ScalarCaster::make(ScalarType::string),
                ],
                'input'    => '2042-11-11',
                'expected' => '2042-11-11 00:00:00',
            ],
            'array' => [
                'casters' => [
                    ArrayOfCaster::make(ScalarType::string),
                ],
                'input'    => ['string', 1],
                'expected' => ['string', '1'],
            ],
            'dt0' => [
                'casters' => [
                    Dt0Caster::make(DummyDt0::class),
                ],
                'input'    => ['readOnlyOne' => 'readOnlyOne', 'readOnlyTwo' => 'readOnlyTwo', 'mutable' => '2042-11-11T00:00:00.000000Z'],
                'expected' => ['readOnlyOne' => 'readOnlyOne', 'readOnlyTwo' => 'readOnlyTwo', 'mutable' => '2042-11-11T00:00:00.000000Z'],
            ],
            'math' => [
                'casters' => [
                    MathCaster::make(),
                ],
                'input'    => 42,
                'expected' => '42',
            ],
        ];
    }
}
