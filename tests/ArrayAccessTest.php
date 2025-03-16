<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace Tests;

use Carbon\CarbonImmutable;
use fab2s\Dt0\Exception\Dt0Exception;
use ReflectionException;
use Tests\Artifacts\DummyDt0;

class ArrayAccessTest extends TestCase
{
    /**
     * @throws Dt0Exception|ReflectionException
     */
    public function test_offset_get()
    {
        $dt0 = DummyDt0::make(readOnlyOne: 'value1', readOnlyTwo: 'value2', mutable: '2023-11-23');
        foreach ($dt0->toArray() as $key => $value) {
            $this->assertEquals($dt0->$key, $dt0[$key]);
        }
    }

    /**
     * @throws Dt0Exception|ReflectionException
     */
    public function test_offset_set()
    {
        $dt0 = DummyDt0::make(readOnlyOne: 'value1', readOnlyTwo: 'value2', mutable: '2023-11-23');

        $this->assertEquals($dt0->mutable, CarbonImmutable::createFromDate(2023, 11, 23)->setTime(0, 0, 0));

        // also assert proper casting upon value set
        $dt0['mutable'] = '2024-11-23';
        $this->assertEquals($dt0->mutable, CarbonImmutable::createFromDate(2024, 11, 23)->setTime(0, 0, 0));
        $this->assertSame($dt0->mutable, $dt0['mutable']);

        $this->expectException(Dt0Exception::class);
        $dt0['readOnlyOne'] = 'value3';
    }

    /**
     * @throws Dt0Exception|ReflectionException
     */
    public function test_offset_exists()
    {
        $dt0 = DummyDt0::make(readOnlyOne: 'value1', readOnlyTwo: 'value2', mutable: '2023-11-23');
        foreach ($dt0->toArray() as $key => $value) {
            $this->assertTrue(isset($dt0[$key]));
        }

        $this->assertfalse(isset($dt0['i_am_not_set']));
    }

    /**
     * @throws Dt0Exception|ReflectionException
     */
    public function test_offset_unset()
    {
        $dt0 = DummyDt0::make(readOnlyOne: 'value1', readOnlyTwo: 'value2', mutable: '2023-11-23');
        $this->expectException(Dt0Exception::class);
        unset($dt0['fromValidate']);
    }

    /**
     * @throws Dt0Exception
     * @throws ReflectionException
     */
    public function test_get_iterator()
    {
        $dt0 = DummyDt0::make(readOnlyOne: 'value1', readOnlyTwo: 'value2', mutable: '2023-11-23');
        foreach ($dt0 as $key => $value) {
            $this->assertTrue(isset($dt0[$key]));
        }
    }
}
