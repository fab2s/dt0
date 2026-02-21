# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/), and this project adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]

## [1.0.1] - 2026-02-21

### Added

#### Standalone Validator

New `Validator` class powered by Laravel's validation engine — no framework required. Works out of the box in any PHP project, and auto-detects Laravel when present. Validation and translation are fully opt-in and require `illuminate/validation` and `illuminate/translation` (v11+):

```shell
composer require "illuminate/validation:^11.0|^12.0" "illuminate/translation:^11.0|^12.0"
```

In Laravel applications, these packages are already present — no extra installation needed. For deeper Laravel integration including model casting, use [laravel-dt0](https://github.com/fab2s/laravel-dt0) directly.

```php
#[Validate(Validator::class)]
#[Rules(
    email: new Rule('required|email'),
)]
class ContactDto extends Dt0
{
    public readonly string $email;

    #[Rule('required|string|min:2')]
    public readonly string $name;
}

$contact = ContactDto::withValidation(
    email: 'john@example.com',
    name: 'John',
);
```

#### Translation Helpers

Standalone `trans()`, `__()`, and `trans_choice()` helper functions, auto-loaded via Composer and guarded by `function_exists` to avoid conflicts with Laravel.

#### Custom Locale Support

Configure locale and provide custom validation messages by adding a `lang/` directory at the project root with an optional `config.php`.

#### Documentation

README reorganized into dedicated doc pages: `validation.md`, `creating-instances.md`, `extending.md`, `inheritance.md`, `output.md`.

## [1.0.0] - 2025-02-08

### Breaking Changes

#### `Cast` Attribute Signature

The `#[Cast]` attribute now accepts a `both` parameter (third positional argument) for bidirectional casters. This shifts the position of `default`, `renameFrom`, `renameTo`, and `propName`. Users relying on positional arguments will need to update their code to use named arguments.

#### Priority Order Inverted (More Intuitive)

**Casting:** Property-level `#[Cast]` now takes precedence over class-level `#[Casts]`.

**Validation:** Property-level `#[Rule]` now takes precedence over class-level `#[Rules]`, which takes precedence over `#[Validate]` rules.

This allows class-level attributes to define defaults that individual properties can override.

#### Output Renaming Consistency

`toJsonArray()` now applies `renameTo` consistently with `toArray()`. Previously, renaming behavior differed between the two methods.

### Added

#### Strict Types

`declare(strict_types=1)` added to all library files except `Dt0.php`. The base `Dt0` class is deliberately left without strict types to preserve backward compatibility: factory methods (`make`, `fromArray`, `fromJson`, etc.) call `new static(...)` which targets user-defined constructors with typed promoted properties. Enforcing strict types there would break implicit scalar coercion (e.g., `string` to `int`) that users may rely on when hydrating from external sources like form data or query strings.

`ClassCaster` now enforces strict types: passing a scalar value whose type doesn't match the target class constructor will throw a `TypeError` instead of silently coercing.

#### Bidirectional Casting with `both`

New `both` parameter on `#[Cast]` for casters that apply to both input and output. When combined with `in` or `out`, casters are chained using onion ordering (`both` → `in` on input, `out` → `both` on output).

```php
#[Cast(both: JsonCaster::class)]
public readonly array $metadata;
```

#### Attribute Inheritance

Both property-level (`#[Cast]`, `#[Rule]`) and class-level (`#[Casts]`, `#[Rules]`, `#[Validate]`, `#[With]`) attributes now inherit from parent classes. Child classes can override inherited attributes.

#### Output Control with `#[With]`

New `#[With]` attribute for declarative output filtering: include protected properties, call getters, or add computed values.

```php
#[With(
    new WithProp(name: 'total', getter: 'getTotal'),
)]
```

#### New Casters

- `JsonCaster` - Decode JSON on input, encode on output (bidirectional)
- `TrimCaster` - Trim strings with support for `ltrim`, `rtrim`, and custom characters
- `Base64Caster` - Decode base64 on input, encode on output (strict mode by default)
- `CasterCollection` - Chain multiple casters in a pipeline
- `ClassCaster` - Instantiate arbitrary classes

#### Extensibility

All attributes now have corresponding interfaces and abstract classes for custom implementations.

#### Auto-null Defaults

Nullable properties without explicit defaults now default to `null` automatically.

## [0.0.1] - 2024-04-28

Initial release.
