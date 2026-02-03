# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/), and this project adheres to [Semantic Versioning](https://semver.org/).

## [1.0.0] - 2025-02-03

### Breaking Changes

#### Priority Order Inverted (More Intuitive)

**Casting:** Property-level `#[Cast]` now takes precedence over class-level `#[Casts]`.

**Validation:** Property-level `#[Rule]` now takes precedence over class-level `#[Rules]`, which takes precedence over `#[Validate]` rules.

This allows class-level attributes to define defaults that individual properties can override.

#### Output Renaming Consistency

`toJsonArray()` now applies `renameTo` consistently with `toArray()`. Previously, renaming behavior differed between the two methods.

### Added

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
