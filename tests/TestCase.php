<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Tests;

use fab2s\Dt0\Dt0;
use fab2s\Dt0\Exception\Dt0Exception;
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
     * @throws Dt0Exception
     */
    protected function dt0Assertions(Dt0 $dt0): static
    {
        $this->assertTrue($dt0->equal($dt0->clone()));
        $this->assertEquals($dt0->toJsonArray(), $dt0::tryFrom($dt0->toArray())->toJsonArray());
        $this->assertEquals($dt0->toJson(), (string) $dt0);
        $this->assertEquals($dt0->toJsonArray(), $dt0::tryFrom($dt0->toJson())->toJsonArray());
        $this->assertEquals($dt0->toJsonArray(), unserialize(serialize($dt0))->toJsonArray());

        return $this;
    }
}
