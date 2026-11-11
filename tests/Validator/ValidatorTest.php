<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace Tests\Validator;

use ArrayObject;
use fab2s\Dt0\Attribute\Rule;
use fab2s\Dt0\Validator\Validator;
use Illuminate\Container\Container;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use ReflectionProperty;
use Tests\TestCase;

class ValidatorTest extends TestCase
{
    public function test_make_translator(): void
    {
        $translator = Validator::makeTranslator();

        $this->assertSame('en', $translator->getLocale());
        $this->assertSame('en', $translator->getFallback());
    }

    public function test_make_translator_resolves_validation_messages(): void
    {
        $translator = Validator::makeTranslator();

        $result = $translator->get('validation.required', ['attribute' => 'email']);

        $this->assertIsString($result);
        $this->assertStringContainsString('email', $result);
        $this->assertStringContainsString('required', $result);
    }

    public function test_make_translator_returns_key_for_unknown(): void
    {
        $translator = Validator::makeTranslator();

        $this->assertSame('nonexistent.key', $translator->get('nonexistent.key'));
    }

    public function test_make_translator_choice(): void
    {
        $translator = Validator::makeTranslator();
        $result     = $translator->choice('validation.required', 1);

        $this->assertIsString($result);
        $this->assertStringContainsString('required', $result);
    }

    public function test_add_rule_is_fluent(): void
    {
        $validator = new Validator;
        $result    = $validator->addRule('email', new Rule('required|email'));

        $this->assertSame($validator, $result);
    }

    public function test_validate_passes(): void
    {
        $validator = new Validator;
        $validator->addRule('email', new Rule('required|email'));

        $result = $validator->validate(['email' => 'test@example.com']);

        $this->assertSame(['email' => 'test@example.com'], $result);
    }

    public function test_validate_passes_with_multiple_rules(): void
    {
        $validator = new Validator;
        $validator->addRule('name', new Rule('required|string|min:2'));
        $validator->addRule('age', new Rule('required|integer|min:0'));

        $result = $validator->validate(['name' => 'John', 'age' => 30]);

        $this->assertSame(['name' => 'John', 'age' => 30], $result);
    }

    public function test_validate_fails(): void
    {
        $this->expectException(ValidationException::class);

        $validator = new Validator;
        $validator->addRule('email', new Rule('required|email'));
        $validator->validate(['email' => 'not-an-email']);
    }

    public function test_validate_fails_missing_required(): void
    {
        $this->expectException(ValidationException::class);

        $validator = new Validator;
        $validator->addRule('name', new Rule('required'));
        $validator->validate([]);
    }

    public function test_validate_no_rules(): void
    {
        $validator = new Validator;

        $this->assertSame([], $validator->validate(['anything' => 'goes']));
    }

    public function test_make_translator_loads_custom_lang_dir(): void
    {
        $langDir = dirname(__DIR__, 2) . '/lang';
        $enDir   = $langDir . '/en';

        mkdir($enDir, 0777, true);
        file_put_contents($enDir . '/validation.php', "<?php\nreturn ['required' => 'CUSTOM required for :attribute.'];\n");

        try {
            $translator = Validator::makeTranslator();
            $result     = $translator->get('validation.required', ['attribute' => 'test']);

            $this->assertSame('CUSTOM required for test.', $result);
        } finally {
            unlink($enDir . '/validation.php');
            rmdir($enDir);
            rmdir($langDir);
        }
    }

    public function test_translator_falls_back_to_standalone_when_nothing_is_bound(): void
    {
        Container::setInstance(null);

        try {
            $translator = Validator::translator();

            $this->assertInstanceOf(Translator::class, $translator);
            $this->assertSame('en', $translator->getLocale());
            $this->assertSame($translator, Validator::translator());
            $this->assertSame('The email field is required.', $translator->get('validation.required', ['attribute' => 'email']));
        } finally {
            Container::setInstance(null);
        }
    }

    public function test_translator_uses_the_bound_translator_and_never_caches_it(): void
    {
        try {
            $first     = new Translator(new ArrayLoader, 'fr');
            $container = new Container;
            $container->instance('translator', $first);
            Container::setInstance($container);

            $this->assertSame($first, Validator::translator());

            // the app is rebuilt: the new translator is picked up
            $second    = new Translator(new ArrayLoader, 'de');
            $container = new Container;
            $container->instance('translator', $second);
            Container::setInstance($container);

            $this->assertSame($second, Validator::translator());

            // nothing bound anymore: back to standalone
            Container::setInstance(null);

            $this->assertNotSame($second, Validator::translator());
            $this->assertSame('en', Validator::translator()->getLocale());
        } finally {
            Container::setInstance(null);
        }
    }

    public function test_translator_ignores_a_bound_service_that_is_not_a_translator(): void
    {
        try {
            // a non-Laravel container may use `translator` for something else
            $container = new Container;
            $container->instance('translator', new ArrayObject);
            Container::setInstance($container);

            $this->assertInstanceOf(Translator::class, Validator::translator());
        } finally {
            Container::setInstance(null);
        }
    }

    public function test_standalone_factory_honours_a_subclass_translator_after_trans(): void
    {
        // constructing it resolves the shared factory, flushed below
        $subclass = new class extends Validator
        {
            public static function makeTranslator(): Translator
            {
                $translator = parent::makeTranslator();
                $translator->addLines(['validation.required' => 'CUSTOM :attribute'], 'en');

                return $translator;
            }
        };

        Validator::flush();
        Container::setInstance(null);

        try {
            // what trans() does first: build and cache the standalone translator
            Validator::translator();

            // the factory is resolved again, by the subclass
            $validator = new $subclass;
            $validator->addRule('name', new Rule('required'));

            $resolved = (new ReflectionProperty(Validator::class, 'factory'))->getValue();
            $this->assertInstanceOf(Factory::class, $resolved);

            try {
                $validator->validate([]);
                $this->fail('validation should have failed');
            } catch (ValidationException $e) {
                $this->assertSame('CUSTOM name', $e->errors()['name'][0]);
            }
        } finally {
            Validator::flush();
            Container::setInstance(null);
        }
    }

    public function test_flush_forgets_the_factory_and_the_standalone_translator(): void
    {
        Container::setInstance(null);

        try {
            new Validator;
            $translator = Validator::translator();
            $factory    = new ReflectionProperty(Validator::class, 'factory');

            $this->assertNotNull($factory->getValue());

            Validator::flush();

            $this->assertNull($factory->getValue());
            $this->assertNotSame($translator, Validator::translator());

            // validators resolve again on next use
            $validator = new Validator;
            $validator->addRule('name', new Rule('required'));
            $this->assertSame(['name' => 'Ada'], $validator->validate(['name' => 'Ada']));
        } finally {
            Validator::flush();
            Container::setInstance(null);
        }
    }

    public function test_declaring_fqn(): void
    {
        $validator = new Validator;
        $fqn       = 'Tests\Validator\ValidatorTest';
        $validator->setDeclaringFqn($fqn);

        $this->assertSame($fqn, $validator->getDeclaringFqn());
    }
}
