<?php

/*
 * This file is part of fab2s/Dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/Dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Tests;

use fab2s\Dt0\Dt0;
use JsonException;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function setUp(): void
    {
        // Turn on error reporting
        error_reporting(E_ALL);
        parent::setUp();
    }

    /**
     * @throws JsonException
     */
    protected function dt0Assertions(Dt0 $dt0): static
    {
        $this->assertSame($dt0->toArray(), $dt0->clone()->toArray());
        $this->assertSame($dt0->toArray(), $dt0::tryFrom($dt0->toArray())->toArray());
        $this->assertSame($dt0->toJson(), (string) $dt0);
        $this->assertSame($dt0->toArray(), $dt0::tryFrom($dt0->toJson())->toArray());
        $this->assertSame($dt0->toArray(), unserialize(serialize($dt0))->toArray());

        return $this;
    }
}
