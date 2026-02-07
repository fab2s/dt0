<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace Tests\Attribute;

use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Caster\CasterCollection;
use fab2s\Dt0\Caster\ScalarCaster;
use fab2s\Dt0\Caster\ScalarType;
use fab2s\Dt0\Caster\TrimCaster;
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Exception\AttributeException;
use Tests\TestCase;

class CastTest extends TestCase
{
    public function test_casts()
    {
        $cast = new Cast;

        $this->assertNull($cast->in);
        $this->assertNull($cast->out);
        $this->assertSame(Dt0::DT0_NIL, $cast->default);
        $this->assertNull($cast->renameFrom);
        $this->assertNull($cast->renameTo);
        $this->assertNull($cast->getPropName());
        $this->assertFalse($cast->hasDefault);

        $this->expectException(AttributeException::class);
        new Cast(in: 'NotACaster');
    }

    public function test_both_only()
    {
        $cast = new Cast(both: TrimCaster::class);

        $this->assertInstanceOf(TrimCaster::class, $cast->both);
        $this->assertInstanceOf(TrimCaster::class, $cast->in);
        $this->assertInstanceOf(TrimCaster::class, $cast->out);
        $this->assertSame($cast->both, $cast->in);
        $this->assertSame($cast->both, $cast->out);
    }

    public function test_both_with_in()
    {
        $cast = new Cast(
            in: new ScalarCaster(ScalarType::string),
            both: TrimCaster::class,
        );

        $this->assertInstanceOf(TrimCaster::class, $cast->both);
        $this->assertInstanceOf(CasterCollection::class, $cast->in);
        $this->assertSame($cast->both, $cast->out);
    }

    public function test_both_with_out()
    {
        $cast = new Cast(
            out: new ScalarCaster(ScalarType::string),
            both: TrimCaster::class,
        );

        $this->assertInstanceOf(TrimCaster::class, $cast->both);
        $this->assertSame($cast->both, $cast->in);
        $this->assertInstanceOf(CasterCollection::class, $cast->out);
    }

    public function test_both_with_in_and_out()
    {
        $cast = new Cast(
            in: new ScalarCaster(ScalarType::string),
            out: new ScalarCaster(ScalarType::int),
            both: TrimCaster::class,
        );

        $this->assertInstanceOf(TrimCaster::class, $cast->both);
        $this->assertInstanceOf(CasterCollection::class, $cast->in);
        $this->assertInstanceOf(CasterCollection::class, $cast->out);
    }
}
