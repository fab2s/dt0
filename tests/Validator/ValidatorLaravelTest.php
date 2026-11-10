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
use Illuminate\Validation\ValidationException;
use Orchestra\Testbench\TestCase;
use ReflectionProperty;

class ValidatorLaravelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Validator::flush();
    }

    protected function tearDown(): void
    {
        Validator::flush();
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

    public function test_translator_is_the_app_translator(): void
    {
        $this->app->setLocale('fr');
        $this->app['translator']->addLines(['dt0.hello' => 'Bonjour :name'], 'fr');

        $this->assertSame($this->app['translator'], Validator::translator());
        $this->assertSame('Bonjour Ada', Validator::translator()->get('dt0.hello', ['name' => 'Ada']));
    }

    public function test_flush_makes_existing_validators_use_the_current_app(): void
    {
        // built once, as when a Dt0 class is compiled
        $validator = new Validator;
        $validator->addRule('email', new Rule('required'));
        $firstApp = app('validator');

        // the app is rebuilt, as between two tests, and speaks French
        $this->refreshApplication();
        $this->app->setLocale('fr');
        $this->app['translator']->addLines(['validation.required' => 'Le champ :attribute est obligatoire.'], 'fr');

        // still the first app's factory, and its English messages
        $this->assertSame($firstApp, $this->factory());
        $this->assertSame('The email field is required.', $this->firstError($validator));

        Validator::flush();

        $this->assertSame('Le champ email est obligatoire.', $this->firstError($validator));
        $this->assertSame($this->app['validator'], $this->factory());
    }

    protected function factory(): mixed
    {
        return (new ReflectionProperty(Validator::class, 'factory'))->getValue();
    }

    protected function firstError(Validator $validator): string
    {
        try {
            $validator->validate([]);
        } catch (ValidationException $e) {
            return $e->errors()['email'][0];
        }

        $this->fail('validation should have failed');
    }
}
