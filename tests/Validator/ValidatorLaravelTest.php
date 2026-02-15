<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace Tests\Validator;

use fab2s\Dt0\Attribute\Rule;
use fab2s\Dt0\Validator\Validator;
use Illuminate\Validation\Factory;
use Orchestra\Testbench\TestCase;
use ReflectionProperty;

class ValidatorLaravelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->resetFactory();
    }

    protected function tearDown(): void
    {
        $this->resetFactory();
        parent::tearDown();
    }

    public function test_resolve_factory_uses_laravel_validator(): void
    {
        $validator = new Validator;

        $ref     = new ReflectionProperty(Validator::class, 'factory');
        $factory = $ref->getValue();

        $this->assertInstanceOf(Factory::class, $factory);
        $this->assertSame(app('validator'), $factory);
    }

    public function test_validate_with_laravel(): void
    {
        $validator = new Validator;
        $validator->addRule('email', new Rule('required|email'));

        $result = $validator->validate(['email' => 'test@example.com']);

        $this->assertSame(['email' => 'test@example.com'], $result);
    }

    protected function resetFactory(): void
    {
        $ref = new ReflectionProperty(Validator::class, 'factory');
        $ref->setValue(null, null);
    }
}
