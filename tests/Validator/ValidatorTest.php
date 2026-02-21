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
use Illuminate\Validation\ValidationException;
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

    public function test_declaring_fqn(): void
    {
        $validator = new Validator;
        $fqn       = 'Tests\Validator\ValidatorTest';
        $validator->setDeclaringFqn($fqn);

        $this->assertSame($fqn, $validator->getDeclaringFqn());
    }
}
