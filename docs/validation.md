# Validation

Dt0 provides a validation architecture based on attributes, with a built-in standalone validator powered by Laravel's validation engine — no Laravel framework required.

## Table of Contents

- [Overview](#overview)
- [Attributes](#attributes)
    - [Rule](#rule)
    - [Rules](#rules)
    - [Validate](#validate)
- [Built-in Standalone Validator](#built-in-standalone-validator)
    - [Installation](#installation)
    - [Usage](#usage)
    - [Laravel Auto-Detection](#laravel-auto-detection)
    - [Locale and Translations](#locale-and-translations)
- [Custom Validators](#custom-validators)
- [Helper Functions](#helper-functions)

## Overview

Validation in Dt0 is driven by three attributes that define rules at different levels, and a `ValidatorInterface` that performs the actual validation. Rules are collected from all levels with a clear priority order:

1. **Property-level** `#[Rule]` — highest priority
2. **Class-level** `#[Rules]` — medium priority
3. **Rules in `#[Validate]`** — lowest priority

```php
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Attribute\Validate;
use fab2s\Dt0\Attribute\Rule;
use fab2s\Dt0\Attribute\Rules;
use fab2s\Dt0\Validator\Validator;

#[Validate(Validator::class)]
#[Rules(
    email: new Rule('required|email'),
)]
class ContactDto extends Dt0
{
    public readonly string $email;

    #[Rule('required|string|min:2|max:100')]
    public readonly string $name;

    #[Rule('nullable|string|max:1000')]
    public readonly ?string $message;
}

// Validate before construction — throws ValidationException on failure
$contact = ContactDto::withValidation(
    email: 'john@example.com',
    name: 'John',
    message: 'Hello!',
);
```

## Attributes

### Rule

`#[Rule]` is a property-level attribute that defines a validation rule for a single property.

```php
use fab2s\Dt0\Attribute\Rule;

class UserDto extends Dt0
{
    #[Rule('required|email')]
    public readonly string $email;

    #[Rule('required|string|min:2')]
    public readonly string $name;

    #[Rule('nullable|integer|min:0|max:150')]
    public readonly ?int $age;
}
```

The `rule` value can be anything your validator understands — a pipe-separated string, an array, or a rule object depending on the validator implementation.

### Rules

`#[Rules]` is a class-level attribute that groups multiple `Rule` definitions:

```php
use fab2s\Dt0\Attribute\Rules;
use fab2s\Dt0\Attribute\Rule;

#[Rules(
    email: new Rule('required|email'),
    name: new Rule('required|string|min:2'),
    age: new Rule('nullable|integer'),
)]
class UserDto extends Dt0
{
    public readonly string $email;
    public readonly string $name;
    public readonly ?int $age;
}
```

Property names are inferred from the named arguments. You can also use the `propName` parameter for positional arguments:

```php
#[Rules(
    new Rule('required|email', propName: 'email'),
    new Rule('required|string', propName: 'name'),
)]
```

### Validate

`#[Validate]` is a class-level attribute that binds a validator implementation to the DTO. It accepts a validator instance or class name, and optionally a `Rules` collection:

```php
use fab2s\Dt0\Attribute\Validate;
use fab2s\Dt0\Attribute\Rules;
use fab2s\Dt0\Attribute\Rule;
use fab2s\Dt0\Validator\Validator;

#[Validate(
    validator: Validator::class,
    rules: new Rules(
        email: new Rule('required|email'),
    ),
)]
class ContactDto extends Dt0
{
    public readonly string $email;
}
```

Rules defined here have the lowest priority — they are overridden by `#[Rules]` at the class level and `#[Rule]` at the property level.

## Built-in Standalone Validator

Dt0 ships with a standalone validator (`fab2s\Dt0\Validator\Validator`) that uses Laravel's validation engine (`illuminate/validation`) without requiring the Laravel framework.

### Installation

Add the illuminate packages to your project (v11+):

```shell
composer require "illuminate/validation:^11.0|^12.0" "illuminate/translation:^11.0|^12.0"
```

### Usage

Reference `Validator::class` in the `#[Validate]` attribute and use `withValidation()` to create validated instances:

```php
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Attribute\Validate;
use fab2s\Dt0\Attribute\Rule;
use fab2s\Dt0\Attribute\Rules;
use fab2s\Dt0\Validator\Validator;

#[Validate(Validator::class)]
#[Rules(
    title: new Rule('required|string|max:255'),
)]
class ArticleDto extends Dt0
{
    public readonly string $title;

    #[Rule('required|string')]
    public readonly string $body;

    #[Rule('nullable|url')]
    public readonly ?string $sourceUrl;
}

// Passes validation — returns ArticleDto instance
$article = ArticleDto::withValidation(
    title: 'Hello World',
    body: 'Some content',
    sourceUrl: 'https://example.com',
);

// Fails validation — throws Illuminate\Validation\ValidationException
try {
    $article = ArticleDto::withValidation(
        title: '',    // required fails
        body: 'text',
        sourceUrl: 'not-a-url',  // url fails
    );
} catch (\Illuminate\Validation\ValidationException $e) {
    $e->errors();
    // [
    //     'title'     => ['The title field is required.'],
    //     'sourceUrl' => ['The source url field must be a valid URL.'],
    // ]

    $e->getMessage();
    // "The title field is required. (and 1 more error)"
}
```

All [Laravel validation rules](https://laravel.com/docs/validation#available-validation-rules) are supported: `required`, `email`, `min`, `max`, `regex`, `unique` (with custom resolver), etc.

### Laravel Auto-Detection

When used inside a Laravel application, the built-in `Validator` automatically detects the framework and delegates to Laravel's own validation factory. This means your DTOs work identically in both contexts — standalone projects get a self-contained validator, while Laravel projects benefit from the framework's full validation ecosystem (custom rules registered in service providers, database-dependent rules like `unique` and `exists`, etc.).

The detection is transparent and requires no configuration: the `Validator` checks for Laravel's `app()` helper and resolves the `validator` service from the container. If that fails for any reason (not in Laravel, container not booted, etc.), it falls back to the standalone implementation.

The resolved validation factory is cached statically, so the detection and setup only happen once per process regardless of how many DTOs are validated.

> If you are using Dt0 in a Laravel project, consider using [laravel-dt0](https://github.com/fab2s/laravel-dt0) which provides deeper integration including model casting.

### Locale and Translations

The standalone validator loads English validation messages from the `illuminate/translation` package by default. You can configure the locale and provide custom translations by creating a `lang/` directory at your project root (next to `vendor/`):

```
project/
├── lang/
│   ├── config.php        ← locale configuration
│   ├── en/
│   │   └── validation.php
│   └── fr/
│       └── validation.php
├── src/
└── vendor/
```

**`lang/config.php`** — optional, defaults to English:

```php
<?php

return [
    'locale'   => 'fr',
    'fallback' => 'en',
];
```

**`lang/fr/validation.php`** — custom translations following [Laravel's format](https://laravel.com/docs/localization):

```php
<?php

return [
    'required' => 'Le champ :attribute est obligatoire.',
    'email'    => 'Le champ :attribute doit être une adresse email valide.',
    // ...
];
```

When a `lang/` directory exists, its translations are loaded as overrides on top of the built-in English messages.

## Custom Validators

You can implement your own validator by implementing `ValidatorInterface`:

```php
use fab2s\Dt0\Attribute\Rule;
use fab2s\Dt0\Validator\ValidatorInterface;
use fab2s\Dt0\Concern\HasDeclaringFqn;

class SymfonyValidator implements ValidatorInterface
{
    use HasDeclaringFqn;

    /** @var array<string, Rule> */
    protected array $rules = [];

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function validate(array $data): array
    {
        // Your validation logic here
        // Throw on failure, return validated data on success
        return $data;
    }

    public function addRule(string $name, Rule $rule): static
    {
        $this->rules[$name] = $rule;

        return $this;
    }
}
```

The `validate()` method receives the input data as an associative array and should:
- **On success**: return the validated data (may be a subset of the input)
- **On failure**: throw an exception

The `addRule()` method is called during DTO compilation to register each property's rule with the validator. The `Rule::$rule` property contains whatever value was passed to the `#[Rule]` attribute — its type and format are up to your validator.

## Helper Functions

When used outside of Laravel, Dt0 provides standalone `trans()`, `__()`, and `trans_choice()` helper functions. These are auto-loaded via Composer and guarded by `function_exists` checks, so they are silently skipped in Laravel applications where the framework provides its own.

```php
// Get the Translator instance
$translator = trans();

// Translate a key
echo trans('validation.required', ['attribute' => 'email']);
// "The email field is required."

// Translate with pluralization
echo trans_choice('{1} :count item|[2,*] :count items', 5);
// "5 items"

// Alias for trans()
echo __('validation.email', ['attribute' => 'address']);
```

These helpers use `Validator::makeTranslator()` under the hood, which is also available directly if you need a `Translator` instance without the global functions:

```php
use fab2s\Dt0\Validator\Validator;

$translator = Validator::makeTranslator();
$translator->getLocale();   // 'en' (or configured locale)
$translator->get('validation.required', ['attribute' => 'name']);
```
