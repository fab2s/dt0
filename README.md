# Dt0

[![CI](https://github.com/fab2s/dt0/actions/workflows/ci.yml/badge.svg)](https://github.com/fab2s/dt0/actions/workflows/ci.yml) [![QA](https://github.com/fab2s/dt0/actions/workflows/qa.yml/badge.svg)](https://github.com/fab2s/dt0/actions/workflows/qa.yml) [![codecov](https://codecov.io/gh/fab2s/dt0/graph/badge.svg?token=VRX16UUB7Y)](https://codecov.io/gh/fab2s/dt0) [![PHPStan](https://img.shields.io/badge/PHPStan-level%209-brightgreen.svg?style=flat)](https://phpstan.org/) [![Latest Stable Version](http://poser.pugx.org/fab2s/dt0/v)](https://packagist.org/packages/fab2s/dt0) [![Total Downloads](http://poser.pugx.org/fab2s/dt0/downloads)](https://packagist.org/packages/fab2s/dt0) [![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg?style=flat)](http://makeapullrequest.com) [![License](http://poser.pugx.org/fab2s/dt0/license)](https://packagist.org/packages/fab2s/dt0)

**Immutable PHP DTOs with bidirectional casting. No framework required. [~8x faster](#benchmarks) than the alternative.**

`Dt0` (_DeeTO_) is a PHP 8.1+ [Data Transfer Object](https://en.wikipedia.org/wiki/Data_transfer_object) implementation that uses native `readonly` properties for true immutability. One `#[Cast]` attribute handles input transformation, output formatting, defaults, and property renaming. Compiled once per class, fast always.

## Quick Start

```php
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Caster\DateTimeCaster;
use fab2s\Dt0\Caster\DateTimeFormatCaster;

class UserDto extends Dt0
{
    public readonly int $id;
    public readonly string $name;
    public readonly string $email;

    #[Cast(
        in: DateTimeCaster::class,
        out: new DateTimeFormatCaster('Y-m-d'),
    )]
    public readonly DateTimeImmutable $createdAt;

    #[Cast(default: 'user')]
    public readonly string $role;
}

// Create from anything
$user = UserDto::make(id: 1, name: 'Jane', email: 'jane@example.com', createdAt: '2024-01-15');
$user = UserDto::fromArray($apiResponse);
$user = UserDto::fromJson($jsonString);

// Access properties
$user->name;       // 'Jane'
$user->createdAt;  // DateTimeImmutable instance
$user->role;       // 'user' (default applied)

// Output with casting applied
$user->toArray();  // [..., 'createdAt' => DateTimeImmutable, ...]
$user->toJson();   // {..., "createdAt": "2024-01-15", ...}

// Immutable updates
$admin = $user->update(role: 'admin');
$user->role;   // 'user' (unchanged)
$admin->role;  // 'admin' (new instance)
```

## Installation

```shell
composer require fab2s/dt0
```

For Laravel model casting integration, see [laravel-dt0](https://github.com/fab2s/laravel-dt0).

## Why Dt0

- **Real immutability, enforced by PHP.** Native `readonly` properties — accidental writes cause fatal errors, not silent bugs.
- **One attribute to rule them all.** `#[Cast]` handles input transformation, output formatting, defaults, and property renaming in a single, composable attribute.
- **Framework-agnostic.** Use anywhere PHP runs — including [standalone validation](./docs/validation.md) powered by Laravel's validation engine, without the framework.
- **Compiled once, fast always.** Reflection and metadata processed once per class, then cached. [~8x faster than spatie/laravel-data](#benchmarks) for typical operations.

## Creating Instances

| Method | Input | On Failure |
|--------|-------|------------|
| `make(...$args)` | Named/positional args | Throws |
| `fromArray(array)` | Associative array | Throws |
| `fromJson(string)` | JSON string | Throws |
| `fromGz(string)` | Gzipped JSON | Throws |
| `from(mixed)` | Array, JSON, or Dt0 | Throws |
| `tryFrom(mixed)` | Array, JSON, or Dt0 | Returns `null` |

Custom constructors with promoted properties are supported. See [Creating Instances](./docs/creating-instances.md) for constructors, `new` vs factory methods, and edge cases.

## Casting

Dt0 supports bidirectional casting: transform values on the way **in** (hydration) and **out** (serialization).

```php
use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Caster\DateTimeCaster;
use fab2s\Dt0\Caster\DateTimeFormatCaster;
use fab2s\Dt0\Caster\ScalarCaster;
use fab2s\Dt0\Caster\ScalarType;

class ArticleDto extends Dt0
{
    public readonly string $title;

    #[Cast(in: new ScalarCaster(ScalarType::int))]
    public readonly int $viewCount;

    #[Cast(
        in: DateTimeCaster::class,
        out: new DateTimeFormatCaster(DateTimeFormatCaster::ISO),
    )]
    public readonly DateTimeImmutable $publishedAt;
}

$article = ArticleDto::make(
    title: 'Hello World',
    viewCount: '42',           // string -> int
    publishedAt: '2024-01-15', // string -> DateTimeImmutable
);
```

Class-level casting with `#[Casts]`:

```php
#[Casts(
    status: new Cast(default: 'pending'),
    priority: new Cast(default: 0),
    createdAt: new Cast(in: DateTimeCaster::class),
)]
class TaskDto extends Dt0
{
    public readonly string $title;
    public readonly string $status;
    public readonly int $priority;
    public readonly DateTime $createdAt;
}
```

**Built-in types** — Enums and nested Dt0 classes are handled automatically, no caster needed:

```php
class OrderDto extends Dt0
{
    public readonly Status $status;      // BackedEnum — auto-cast from string/int
    public readonly AddressDto $address; // Nested Dt0 — auto-cast from array/JSON
}
```

### Available Casters

| Caster | Description |
|--------|-------------|
| [`ScalarCaster`](./src/Caster/ScalarCaster.php) | Cast to `int`, `float`, `bool`, `string` |
| [`JsonCaster`](./src/Caster/JsonCaster.php) | Decode JSON on input, encode on output |
| [`TrimCaster`](./src/Caster/TrimCaster.php) | Trim strings (`ltrim`, `rtrim`, custom characters) |
| [`Base64Caster`](./src/Caster/Base64Caster.php) | Decode base64 on input, encode on output |
| [`DateTimeCaster`](./src/Caster/DateTimeCaster.php) | Parse to `DateTime` or `DateTimeImmutable` |
| [`DateTimeFormatCaster`](./src/Caster/DateTimeFormatCaster.php) | Format DateTime for output |
| [`CarbonCaster`](./src/Caster/CarbonCaster.php) | Parse to Carbon (requires `nesbot/carbon`) |
| [`Dt0Caster`](./src/Caster/Dt0Caster.php) | Cast to nested Dt0 instances |
| [`ArrayOfCaster`](./src/Caster/ArrayOfCaster.php) | Cast typed arrays (Dt0, Enum, or scalar) |
| [`ClassCaster`](./src/Caster/ClassCaster.php) | Instantiate arbitrary classes |
| [`MathCaster`](./src/Caster/MathCaster.php) | Precision numbers (requires `fab2s/math`) |
| [`CasterCollection`](./src/Caster/CasterCollection.php) | Chain multiple casters in a pipeline |

See [Casters Documentation](./docs/casters.md) for detailed usage, bidirectional casting, and custom casters.

## Validation

Dt0 provides [standalone validation](./docs/validation.md) powered by Laravel's validation engine — no Laravel framework required. In Laravel applications, it auto-detects the framework and uses Laravel's validator transparently.

Requires `illuminate/validation` and `illuminate/translation` (v11+):

```shell
composer require "illuminate/validation:^11.0|^12.0" "illuminate/translation:^11.0|^12.0"
```

```php
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Attribute\Rule;
use fab2s\Dt0\Attribute\Rules;
use fab2s\Dt0\Attribute\Validate;
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

// Throws ValidationException on failure
$contact = ContactDto::withValidation(
    email: 'test@example.com',
    name: 'John',
    message: 'Hello!',
);
```

Rules can be defined at three levels with clear priority: property `#[Rule]` > class `#[Rules]` > `#[Validate]` rules. The `ValidatorInterface` is open for custom implementations.

See [Validation Documentation](./docs/validation.md) for locale configuration, custom translations, and custom validators.

## Output

```php
$dto->toArray();      // Array with objects intact, output casters applied
$dto->toJsonArray();  // Array with objects serialized (JsonSerializable)
$dto->toJson();       // JSON string
$dto->toGz();         // Gzipped JSON string
json_encode($dto);    // JSON (implements JsonSerializable)
(string) $dto;        // JSON (implements Stringable)
```

### Output Filtering

```php
// Exclude sensitive fields
$dto->without('password', 'apiKey')->toJson();

// Include only specific fields
$dto->only('id', 'name')->toArray();

// Add computed or protected properties
$dto->with('fullName', fn($d) => "$d->firstName $d->lastName")->toArray();
$dto->with('total', true)->toArray();       // calls getTotal()
$dto->with('internalField')->toArray();     // exposes protected property
```

Declarative output control with `#[With]` is also supported. See [Output Documentation](./docs/output.md) for details.

## Immutable Operations

```php
$copy = $dto->clone();
$updated = $dto->update(name: 'Jane', role: 'admin');
$dto->equals($updated);  // false

// Serialization round-trip
$restored = unserialize(serialize($dto));
$dto->equals($restored);  // true
```

## Property Renaming

Map between external names (APIs, databases) and internal property names:

```php
class ApiResponseDto extends Dt0
{
    #[Cast(renameFrom: 'created_at', renameTo: 'createdAtStr')]
    public readonly string $createdAt;

    // Accept multiple input names
    #[Cast(renameFrom: ['user_name', 'username', 'login'])]
    public readonly string $userName;
}
```

All `renameTo` values are automatically added to `renameFrom`, ensuring round-trip consistency.

## Default Values

```php
class ConfigDto extends Dt0
{
    #[Cast(default: 3600)]
    public readonly int $ttl;

    #[Cast(default: null)]
    public readonly ?string $prefix;

    #[Cast(default: true)]
    public readonly bool $enabled;
}

$config = ConfigDto::make();  // All defaults applied
```

**Resolution order**: provided value > Cast default > nullable default > promoted parameter default.

## Attribute Inheritance

Dt0 resolves attributes up the parent class chain — both property-level and class-level:

```php
class TimestampedDto extends Dt0
{
    #[Cast(in: DateTimeCaster::class, out: new DateTimeFormatCaster(DateTimeFormatCaster::ISO))]
    public readonly DateTimeImmutable $createdAt;
}

// Inherits Cast from TimestampedDto
class ArticleDto extends TimestampedDto
{
    public readonly string $title;
    public readonly DateTimeImmutable $createdAt;  // Redeclare for PHP < 8.4
}

$article = ArticleDto::make(title: 'Hello', createdAt: '2024-01-15');
$article->createdAt;  // DateTimeImmutable (inherited cast applied)
```

See [Inheritance Documentation](./docs/inheritance.md) for multi-level inheritance, class attribute inheritance, and override patterns.

## Performance

Reflection and attribute metadata compiled **once per class, per process**. Subsequent instantiations reuse cached data with zero reflection overhead.

### Benchmarks

<a id="benchmarks"></a>

#### Dt0 vs spatie/laravel-data (PHP 8.4, 10,000 iterations)

| Operation | Dt0 | spatie/laravel-data | Speedup |
|-----------|-----|---------------------|---------|
| Simple DTO (8 props, 5 casts) | 141.6 µs | 1,158 µs | **~8.2x faster** |
| Complex DTO (nested + arrays) | 741.9 µs | 3,628 µs | **~4.9x faster** |
| Round-trip (json->dto->json) | 248.4 µs | 2,004 µs | **~8.1x faster** |

**Repeated serialization (same instance):**

| Operation | Dt0 | spatie/laravel-data | Speedup |
|-----------|-----|---------------------|---------|
| toArray() (simple) | 3.6 µs | 679.4 µs | **~188.7x faster** |
| toArray() (nested) | 3.6 µs | 2,056 µs | **~571.1x faster** |
| toJson() | 2.8 µs | 681.8 µs | **~243.5x faster** |

Output caching delivers 188-571x improvements when serializing the same instance multiple times (API + logging, event sourcing, queue + monitoring, caching layers).

```shell
php benchmark/compare-spatie.php
```

## Extending

Dt0's attributes, casters, and validators are extensible. See [Extending Documentation](./docs/extending.md) for interfaces, abstract classes, and compiled metadata access.

## Exceptions

All exceptions extend [`ContextException`](https://github.com/fab2s/ContextException) with structured context for debugging:

| Exception | Usage |
|-----------|-------|
| `Dt0Exception` | General DTO errors (missing properties, invalid input) |
| `CasterException` | Casting failures |
| `AttributeException` | Attribute configuration errors |

## Requirements

- PHP 8.1, 8.2, 8.3, or 8.4

## Dependencies

- [`fab2s/context-exception`](https://github.com/fab2s/ContextException) - Contextual exceptions
- [`fab2s/enumerate`](https://github.com/fab2s/Enumerate) - Enum utilities

### Optional

- [`illuminate/validation`](https://github.com/illuminate/validation) + [`illuminate/translation`](https://github.com/illuminate/translation) - For standalone `Validator`
- [`nesbot/carbon`](https://github.com/briannesbitt/Carbon) - For `CarbonCaster`
- [`fab2s/math`](https://github.com/fab2s/Math) - For `MathCaster`

## Contributing

Contributions are welcome. Please open issues and submit pull requests.

```shell
composer fix        # Code style (Laravel Pint)
composer test       # Run tests
composer cov        # Tests with coverage
composer stan       # PHPStan level 9 (src/)
composer stan-tests # PHPStan level 5 (tests/)
```

## License

Dt0 is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
