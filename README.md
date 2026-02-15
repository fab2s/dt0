# Dt0

[![CI](https://github.com/fab2s/dt0/actions/workflows/ci.yml/badge.svg)](https://github.com/fab2s/dt0/actions/workflows/ci.yml) [![QA](https://github.com/fab2s/dt0/actions/workflows/qa.yml/badge.svg)](https://github.com/fab2s/dt0/actions/workflows/qa.yml) [![codecov](https://codecov.io/gh/fab2s/dt0/graph/badge.svg?token=VRX16UUB7Y)](https://codecov.io/gh/fab2s/dt0) [![PHPStan](https://img.shields.io/badge/PHPStan-level%209-brightgreen.svg?style=flat)](https://phpstan.org/) [![Latest Stable Version](http://poser.pugx.org/fab2s/dt0/v)](https://packagist.org/packages/fab2s/dt0) [![Total Downloads](http://poser.pugx.org/fab2s/dt0/downloads)](https://packagist.org/packages/fab2s/dt0) [![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg?style=flat)](http://makeapullrequest.com) [![License](http://poser.pugx.org/fab2s/dt0/license)](https://packagist.org/packages/fab2s/dt0)

`Dt0` (_DeeTO_ or _DeTZerO_) is a PHP 8.1+ [Data Transfer Object](https://en.wikipedia.org/wiki/Data_transfer_object) implementation built for true immutability through `readonly` properties, with powerful bidirectional casting and validation.

## Table of Contents

- [Why Dt0](#why-dt0)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Creating Instances](#creating-instances)
  - [Factory Methods](#factory-methods)
  - [Using Constructors](#using-constructors)
  - [`new` vs Factory Methods](#new-vs-factory-methods)
- [Output](#output)
  - [Output Filtering](#output-filtering)
- [Immutable Operations](#immutable-operations)
- [Casting](#casting)
  - [Property-Level Casting](#property-level-casting)
  - [Bidirectional Casting](#bidirectional-casting)
  - [Class-Level Casting](#class-level-casting)
  - [Available Casters](#available-casters)
  - [Built-in Type Support](#built-in-type-support)
  - [Custom Casters](#custom-casters)
- [Property Renaming](#property-renaming)
- [Default Values](#default-values)
- [Attribute Inheritance](#attribute-inheritance)
  - [Property Attribute Inheritance](#property-attribute-inheritance)
  - [Class Attribute Inheritance](#class-attribute-inheritance)
- [Validation](#validation)
- [Type System Integration](#type-system-integration)
- [Extending Attributes](#extending-attributes)
- [Performance](#performance)
- [Exceptions](#exceptions)
- [Requirements](#requirements)
- [Contributing](#contributing)
- [License](#license)

## Why Dt0

**Real immutability, enforced by PHP.** Most DTO packages simulate immutability with magic methods. Dt0 uses native `readonly` properties - the language itself prevents modifications. Accidental writes cause fatal errors, not silent bugs.

**One attribute to rule them all.** Where other packages require a dozen attributes for casting, defaults, and renaming, Dt0's `#[Cast]` handles input transformation, output formatting, defaults, and property renaming in a single, composable attribute.

**Framework-agnostic core.** Use it anywhere PHP runs — including [standalone validation](./docs/validation.md) powered by Laravel's validation engine, without the framework. For full Laravel projects, [laravel-dt0](https://github.com/fab2s/laravel-dt0) adds model casting integration.

**Compiled once, fast always.** Reflection and attribute metadata are processed once per class, then cached. Every subsequent instantiation reuses compiled data with zero reflection overhead.

```php
// One attribute does it all
#[Cast(
    in: DateTimeCaster::class,              // Transform on input
    out: new DateTimeFormatCaster('Y-m-d'), // Format on output
    both: JsonCaster::class,                // Same caster for both directions
    default: new DateTime(),                // Default value
    renameFrom: 'created_at',              // Accept external name
)]
public readonly DateTime $createdAt;
```

**Flexible, not dogmatic.** While immutability is the core feature, Dt0 doesn't force it. Use mutable properties when needed. Expose protected properties via `with()`. The package provides capabilities; you decide how to use them.

## Installation

```shell
composer require fab2s/dt0
```

For Laravel, see [Laravel Dt0](https://github.com/fab2s/laravel-dt0) for validation and model attribute casting integration.

## Quick Start

```php
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Attribute\Cast;

class UserDto extends Dt0
{
    public readonly int $id;
    public readonly string $name;
    public readonly string $email;

    #[Cast(default: 'user')]
    public readonly string $role;
}

// Create with named arguments
$user = UserDto::make(
    id: 42,
    name: 'John Doe',
    email: 'john@example.com',
);

// Access properties
$user->id;    // 42
$user->name;  // 'John Doe'
$user->role;  // 'user' (default applied)

// Convert to array/JSON
$user->toArray();  // ['id' => 42, 'name' => 'John Doe', 'email' => 'john@example.com', 'role' => 'user']
$user->toJson();   // {"id":42,"name":"John Doe","email":"john@example.com","role":"user"}

// Immutable update
$admin = $user->update(role: 'admin');
$user->role;   // 'user' (unchanged)
$admin->role;  // 'admin' (new instance)
```

## Creating Instances

### Factory Methods

Dt0 provides multiple ways to create instances:

```php
// Named arguments - order doesn't matter
$dto = UserDto::make(email: 'a@b.com', name: 'John', id: 1);

// From associative array
$dto = UserDto::fromArray([
    'id'    => 1,
    'name'  => 'John',
    'email' => 'john@example.com',
]);

// From JSON string
$dto = UserDto::fromJson('{"id": 1, "name": "John", "email": "john@example.com"}');

// Polymorphic - accepts array, JSON string, or existing Dt0 instance
$dto = UserDto::from($input);       // throws Dt0Exception on failure
$dto = UserDto::tryFrom($input);    // returns null on failure

// From gzipped JSON
$dto = UserDto::fromGz($gzippedData);
```

| Method | Input | On Failure |
|--------|-------|------------|
| `make(...$args)` | Named/positional args | Throws |
| `fromArray(array)` | Associative array | Throws |
| `fromJson(string)` | JSON string | Throws |
| `fromString(string)` | JSON string (alias) | Throws |
| `fromGz(string)` | Gzipped JSON | Throws |
| `from(mixed)` | Array, JSON, or Dt0 | Throws |
| `tryFrom(mixed)` | Array, JSON, or Dt0 | Returns `null` |

### Using Constructors

Dt0 classes can have custom constructors with promoted properties:

```php
class OrderDto extends Dt0
{
    public readonly string $notes;
    public readonly float $total;

    public function __construct(
        public readonly string $orderId,

        #[Cast(in: DateTimeCaster::class)]
        public readonly DateTime $placedAt,

        // Non-promoted parameters can also be casted
        #[Cast(in: ScalarCaster::class)]
        float $subtotal = 0,

        // Required: captures remaining args for other properties
        mixed ...$args,
    ) {
        // Custom logic here
        $this->total = $subtotal * 1.2; // Add tax

        parent::__construct(...$args);
    }
}

// Constructor parameters maintain their order
$order = new OrderDto(
    orderId: 'ORD-123',
    placedAt: new DateTime(),
    subtotal: 100.00,
    notes: 'Gift wrap please',  // Goes to props via ...$args
);

// Factory methods don't care about order
$order = OrderDto::make(
    notes: 'Gift wrap please',
    subtotal: 100.00,
    orderId: 'ORD-123',
    placedAt: '2024-01-15 10:30:00',
);
```

### `new` vs Factory Methods

When using `new` directly with **promoted readonly properties that have a default value**, PHP initializes them immediately, **before** Dt0 can apply casting. Promoted properties without defaults behave normally.

```php
class EventDto extends Dt0
{
    public function __construct(
        // Has default = casting won't apply with `new`
        #[Cast(in: DateTimeCaster::class)]
        public readonly DateTime $date = new DateTime(),

        // No default = casting works fine with `new`
        #[Cast(in: DateTimeCaster::class)]
        public readonly DateTime $endDate,

        mixed ...$args,
    ) {
        parent::__construct(...$args);
    }
}

// ❌ Casting won't apply to $date (has default)
$event = new EventDto(date: '2024-01-15', endDate: new DateTime());  // TypeError for $date

// ✅ Casting works for $endDate (no default)
$event = new EventDto(endDate: '2024-01-15');  // Works, $date uses its default

// ✅ Factory methods always work - casting applies to all properties
$event = EventDto::make(date: '2024-01-15', endDate: '2024-01-16');  // Both cast correctly
```

**Best practice**: Use factory methods (`make`, `from`, `fromArray`, etc.) for full casting support. Reserve `new` for cases where you're passing already-correct types or relying on defaults.

## Output

```php
$dto->toArray();      // Array with objects intact
$dto->toJsonArray();  // Array with objects serialized (JsonSerializable called)
$dto->jsonSerialize();// Same as toJsonArray()
$dto->toJson();       // JSON string
$dto->toGz();         // Gzipped JSON string
json_encode($dto);    // JSON string (implements JsonSerializable)
(string) $dto;        // JSON string (implements Stringable)
```

### Output Filtering

Control which properties appear in output using `with()`, `without()`, and `only()`.

#### Adding Properties with `with()`

By default, only **public** properties are included in output. Use `with()` to add protected properties, call getters, or create computed values.

**Include a protected property:**

```php
class UserDto extends Dt0
{
    public readonly int $id;
    public readonly string $name;
    protected string $internalScore;

    public function setInternalScore(string $score): static
    {
        $this->internalScore = $score;
        return $this;
    }
}

$user = UserDto::make(id: 1, name: 'John');
$user->setInternalScore('A+');

$user->toArray();  // ['id' => 1, 'name' => 'John'] - no internalScore

$user->with('internalScore')->toArray();
// ['id' => 1, 'name' => 'John', 'internalScore' => 'A+']
```

**Call a getter method:**

```php
class ProductDto extends Dt0
{
    public readonly int $price;
    public readonly int $quantity;

    public function getTotal(): int
    {
        return $this->price * $this->quantity;
    }
}

$product = ProductDto::make(price: 100, quantity: 3);

// with('total', true) calls getTotal() automatically
$product->with('total', true)->toArray();
// ['price' => 100, 'quantity' => 3, 'total' => 300]

// Or specify a custom method name
$product->with('total', 'getTotal')->toArray();
// Same result
```

**Add computed values with closures:**

```php
class PersonDto extends Dt0
{
    public readonly string $firstName;
    public readonly string $lastName;
}

$person = PersonDto::make(firstName: 'John', lastName: 'Doe');

$person->with('fullName', fn(PersonDto $dto) => "{$dto->firstName} {$dto->lastName}")
    ->toArray();
// ['firstName' => 'John', 'lastName' => 'Doe', 'fullName' => 'John Doe']
```

**Declarative with `#[With]` attribute:**

```php
use fab2s\Dt0\Attribute\With;
use fab2s\Dt0\Attribute\WithProp;

#[With(
    new WithProp(name: 'internalScore'),
    new WithProp(name: 'total', getter: 'getTotal'),
)]
class OrderDto extends Dt0
{
    public readonly int $price;
    public readonly int $quantity;
    protected string $internalScore = 'pending';

    public function getTotal(): int
    {
        return $this->price * $this->quantity;
    }
}

$order = OrderDto::make(price: 50, quantity: 2);
$order->toArray();
// ['price' => 50, 'quantity' => 2, 'internalScore' => 'pending', 'total' => 100]
```

**`with()` getter options:**

| Call | Behavior |
|------|----------|
| `with('name')` | Access `$this->name` directly |
| `with('name', false)` | Access `$this->name` directly |
| `with('name', true)` | Call `$this->getName()` |
| `with('name', 'customMethod')` | Call `$this->customMethod()` |
| `with('name', fn($dto) => ...)` | Call the closure with `$this` |

#### Excluding Properties with `without()`

```php
class UserDto extends Dt0
{
    public readonly int $id;
    public readonly string $name;
    public readonly string $password;
    public readonly string $apiKey;
}

$user = UserDto::make(/* ... */);

$user->without('password', 'apiKey')->toJson();
// {"id":1,"name":"John"}
```

#### Selecting Specific Properties with `only()`

```php
$user->only('id', 'name')->toArray();
// ['id' => 1, 'name' => 'John']
```

#### Resetting Filters

```php
$user->clearWith();     // Remove all with() additions
$user->clearWithout();  // Remove all without() exclusions
```

## Immutable Operations

```php
// Clone creates an identical copy
$copy = $dto->clone();
$dto->equals($copy);  // true

// Update creates a new instance with changed values
$updated = $dto->update(name: 'Jane', role: 'admin');
$dto->equals($updated);  // false

// Original unchanged
$dto->name;      // 'John'
$updated->name;  // 'Jane'

// Compare instances
$dto->equals($other);  // true if all properties match

// Serialization round-trip
$restored = unserialize(serialize($dto));
$dto->equals($restored);  // true
```

## Casting

Dt0 supports bidirectional casting: transform values on the way **in** (hydration) and **out** (serialization).

### Property-Level Casting

Use the `#[Cast]` attribute on individual properties:

```php
use fab2s\Dt0\Dt0;
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
        in: DateTimeCaster::class,                                // string -> DateTime
        out: new DateTimeFormatCaster(DateTimeFormatCaster::ISO), // DateTime -> ISO string
    )]
    public readonly DateTime $publishedAt;

    #[Cast(
        in: DateTimeCaster::class,
        out: new DateTimeFormatCaster('Y-m-d'),  // Custom format
    )]
    public readonly ?DateTime $updatedAt;
}

$article = ArticleDto::make(
    title: 'Hello World',
    viewCount: '42',              // String cast to int
    publishedAt: '2024-01-15',    // String cast to DateTime
    updatedAt: null,
);

$article->viewCount;              // 42 (int)
$article->publishedAt;            // DateTime instance

$article->toArray();
// ['title' => 'Hello World', 'viewCount' => 42, 'publishedAt' => DateTime, 'updatedAt' => null]

$article->jsonSerialize();
// ['title' => 'Hello World', 'viewCount' => 42, 'publishedAt' => '2024-01-15T00:00:00.000000Z', 'updatedAt' => null]
```

### Bidirectional Casting

When a caster applies to both input and output, use the `both` parameter instead of repeating the same caster for `in` and `out`:

```php
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Caster\JsonCaster;
use fab2s\Dt0\Caster\Base64Caster;

class PayloadDto extends Dt0
{
    #[Cast(both: JsonCaster::class)]
    public readonly array $metadata;

    #[Cast(both: Base64Caster::class)]
    public readonly string $data;
}
```

`both` can be combined with `in` and/or `out` for layered casting. When combined, casters are chained using onion ordering:

- **Input:** `both` → `in`
- **Output:** `out` → `both`

```php
use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Caster\DateTimeCaster;
use fab2s\Dt0\Caster\DateTimeFormatCaster;
use fab2s\Dt0\Caster\TrimCaster;

class EventDto extends Dt0
{
    #[Cast(
        both: new TrimCaster,                          // Trims on input AND output
        in: DateTimeCaster::class,                     // Input: trim → parse DateTime
        out: new DateTimeFormatCaster('Y-m-d H:i:s'), // Output: format → trim
    )]
    public readonly DateTime $startsAt;
}
```

### Class-Level Casting

Define multiple casts at the class level with `#[Casts]`:

```php
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Attribute\Casts;
use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Caster\DateTimeCaster;

#[Casts(
    // Using named arguments (property name => Cast)
    status: new Cast(default: 'pending'),
    priority: new Cast(default: 0),
    createdAt: new Cast(in: DateTimeCaster::class),

    // Or using positional with explicit propName
    new Cast(default: false, propName: 'isArchived'),
)]
class TaskDto extends Dt0
{
    public readonly string $title;
    public readonly string $status;
    public readonly int $priority;
    public readonly DateTime $createdAt;
    public readonly bool $isArchived;
}

$task = TaskDto::make(title: 'Review PR', createdAt: 'now');
$task->status;     // 'pending'
$task->priority;   // 0
$task->isArchived; // false
```

**Combining class and property casts**: You can use both. In case of overlap, property-level `#[Cast]` takes precedence over class-level `#[Casts]`.

```php
#[Casts(
    name: new Cast(default: 'Anonymous'),  // Fallback if no property-level Cast
)]
class PersonDto extends Dt0
{
    #[Cast(default: 'Unknown')]  // Takes precedence
    public readonly string $name;

    #[Cast(default: 0)]  // Applied (no conflict)
    public readonly int $age;
}
```

### Available Casters

| Caster | Description |
|--------|-------------|
| [`ScalarCaster`](./src/Caster/ScalarCaster.php) | Cast to `int`, `float`, `bool`, `string` |
| [`JsonCaster`](./src/Caster/JsonCaster.php) | Decode JSON on input, encode on output |
| [`TrimCaster`](./src/Caster/TrimCaster.php) | Trim strings (supports `ltrim`, `rtrim`, custom characters) |
| [`Base64Caster`](./src/Caster/Base64Caster.php) | Decode base64 on input, encode on output |
| [`DateTimeCaster`](./src/Caster/DateTimeCaster.php) | Parse strings/arrays to `DateTime` or `DateTimeImmutable` |
| [`DateTimeFormatCaster`](./src/Caster/DateTimeFormatCaster.php) | Format DateTime for output |
| [`CarbonCaster`](./src/Caster/CarbonCaster.php) | Parse to Carbon (requires `nesbot/carbon`) |
| [`Dt0Caster`](./src/Caster/Dt0Caster.php) | Cast to nested Dt0 instances |
| [`ArrayOfCaster`](./src/Caster/ArrayOfCaster.php) | Cast arrays of typed items |
| [`ClassCaster`](./src/Caster/ClassCaster.php) | Instantiate arbitrary classes |
| [`MathCaster`](./src/Caster/MathCaster.php) | Precision numbers (requires `fab2s/math`) |
| [`CasterCollection`](./src/Caster/CasterCollection.php) | Chain multiple casters in a pipeline |

See [Casters Documentation](./docs/casters.md) for detailed usage of each caster.

### Built-in Type Support

These types are handled automatically without explicit casters:

**Enums** - Both `UnitEnum` and `BackedEnum`:

```php
enum Status: string {
    case Draft = 'draft';
    case Published = 'published';
}

class PostDto extends Dt0
{
    public readonly string $title;
    public readonly Status $status;  // No caster needed
}

$post = PostDto::make(title: 'Hello', status: 'published');
$post->status;           // Status::Published
$post->jsonSerialize();  // ['title' => 'Hello', 'status' => 'published']
```

**Nested Dt0** - Child Dt0 classes are recognized automatically:

```php
class AddressDto extends Dt0
{
    public readonly string $street;
    public readonly string $city;
}

class PersonDto extends Dt0
{
    public readonly string $name;
    public readonly AddressDto $address;  // No caster needed
}

$person = PersonDto::make(
    name: 'John',
    address: ['street' => '123 Main St', 'city' => 'Boston'],
);

$person->address->city;  // 'Boston'
```

### Custom Casters

Implement [`CasterInterface`](./src/Caster/CasterInterface.php) or extend [`CasterAbstract`](./src/Caster/CasterAbstract.php):

```php
use fab2s\Dt0\Caster\CasterAbstract;
use fab2s\Dt0\Dt0;

class UpperCaseCaster extends CasterAbstract
{
    public function cast(mixed $value, array|Dt0|null $data = null): ?string
    {
        return is_string($value) ? strtoupper($value) : null;
    }
}
```

The `$data` parameter provides context:
- On **input**: The full input array being hydrated
- On **output**: The Dt0 instance being serialized

This enables casters that need multiple values:

```php
class FullNameCaster extends CasterAbstract
{
    public function cast(mixed $value, array|Dt0|null $data = null): ?string
    {
        if (is_array($data)) {
            // Input: combine first and last name
            return trim(($data['firstName'] ?? '') . ' ' . ($data['lastName'] ?? ''));
        }

        if ($data instanceof Dt0) {
            // Output: same logic with object access
            return trim($data->firstName . ' ' . $data->lastName);
        }

        return $value;
    }
}
```

See [Casters Documentation](./docs/casters.md) for more examples.

## Property Renaming

Map between external names (APIs, databases) and internal property names:

```php
class ApiResponseDto extends Dt0
{
    #[Cast(
        renameFrom: 'created_at',  // Accept this name on input
        renameTo: 'createdAtStr',     // Use this name on output
    )]
    public readonly string $createdAt;

    #[Cast(renameFrom: 'user_id')]
    public readonly int $userId;
}

// Input uses external names
$dto = ApiResponseDto::make(
    created_at: '2024-01-15',
    user_id: 42,
);

// Properties use internal names
$dto->createdAt;  // '2024-01-15'
$dto->userId;     // 42

// Output uses renamed keys
$dto->toArray();  // ['createdAtStr' => '2024-01-15', 'userId' => 42]
```

**Multiple input aliases** - Accept several names for the same property:

```php
class UserDto extends Dt0
{
    // First match wins
    #[Cast(renameFrom: ['user_name', 'username', 'login', 'userName'])]
    public readonly string $userName;
}

// All of these work
UserDto::make(user_name: 'john');
UserDto::make(username: 'john');
UserDto::make(login: 'john');
UserDto::make(userName: 'john');
```

**Round-trip consistency**: All `renameTo` values are automatically added to `renameFrom`, ensuring output can always be used as input:

```php
$dto = ApiResponseDto::make(created_at: '2024-01-15', user_id: 42);
$array = $dto->toArray();  // Uses renameTo keys

// This always works
$dto->equals(ApiResponseDto::fromArray($array));  // true
```

## Default Values

Readonly properties can't have default values unless they're promoted constructor parameters. Casts solve this:

```php
class ConfigDto extends Dt0
{
    #[Cast(default: 3600)]
    public readonly int $ttl;

    #[Cast(default: null)]
    public readonly ?string $prefix;

    #[Cast(default: [])]
    public readonly array $tags;

    #[Cast(default: true)]
    public readonly bool $enabled;
}

$config = ConfigDto::make();  // No arguments needed
$config->ttl;      // 3600
$config->prefix;   // null
$config->tags;     // []
$config->enabled;  // true

// Override defaults
$config = ConfigDto::make(ttl: 7200, enabled: false);
$config->ttl;      // 7200
$config->enabled;  // false
```

**Default resolution order**:
1. Value provided during instantiation
2. Default from `Cast` attribute
3. Default from type (nullable types default to `null`)
4. Default from promoted constructor parameter

### The Nil Concept

PHP has no native way to express "never set" vs "set to null". Dt0 uses a null byte (`"\0"`) internally as a sentinel to distinguish these states. This means any value except `"\0"` can be used as a default.

If you genuinely need `"\0"` as a default value (extremely rare), use a promoted constructor parameter instead.

## Attribute Inheritance

Dt0 supports attribute inheritance across class hierarchies, enabling powerful patterns for code reuse.

### Property Attribute Inheritance

When a property doesn't have an attribute, Dt0 walks up the parent class chain looking for the same property with that attribute. This is particularly useful for base DTOs:

```php
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Caster\DateTimeCaster;
use fab2s\Dt0\Caster\DateTimeFormatCaster;

// Base DTO with common timestamp handling
class TimestampedDto extends Dt0
{
    #[Cast(
        in: DateTimeCaster::class,
        out: new DateTimeFormatCaster(DateTimeFormatCaster::ISO),
    )]
    public readonly DateTime $createdAt;

    #[Cast(
        in: DateTimeCaster::class,
        out: new DateTimeFormatCaster(DateTimeFormatCaster::ISO),
    )]
    public readonly ?DateTime $updatedAt;
}

// Child inherits the Cast attributes automatically
class ArticleDto extends TimestampedDto
{
    public readonly string $title;
    public readonly string $content;

    // createdAt and updatedAt inherit their Cast from TimestampedDto
    // Prior to PHP 8.4, you need to redeclare the properties:
    public readonly DateTime $createdAt;
    public readonly ?DateTime $updatedAt;
}

$article = ArticleDto::make(
    title: 'Hello',
    content: 'World',
    createdAt: '2024-01-15 10:30:00',  // String -> DateTime via inherited Cast
    updatedAt: null,
);

$article->createdAt;      // DateTime instance
$article->jsonSerialize();
// createdAt formatted as ISO string thanks to inherited 'out' caster
```

> **PHP 8.4+**: Property hooks make inheritance even cleaner. You no longer need to redeclare parent properties in child classes - they're inherited automatically along with their attributes. The examples above show property redeclaration for compatibility with PHP 8.1-8.3.

**Override inherited attributes** - Child classes can override parent attributes:

```php
class TimestampedDto extends Dt0
{
    #[Cast(
        in: DateTimeCaster::class,
        out: new DateTimeFormatCaster(DateTimeFormatCaster::ISO),
    )]
    public readonly DateTime $createdAt;
}

class CustomArticleDto extends TimestampedDto
{
    public readonly string $title;

    // Override with different output format
    #[Cast(
        in: DateTimeCaster::class,
        out: new DateTimeFormatCaster('Y-m-d'),  // Different format
    )]
    public readonly DateTime $createdAt;
}
```

**Multi-level inheritance** - Attributes are resolved up the entire chain:

```php
class BaseDto extends Dt0
{
    #[Cast(default: 'active')]
    public readonly string $status;
}

class MiddleDto extends BaseDto
{
    // status inherits Cast from BaseDto
    public readonly string $status;  // Redeclare for PHP < 8.4
    public readonly string $type;
}

class FinalDto extends MiddleDto
{
    // status still inherits Cast from BaseDto (through MiddleDto)
    public readonly string $status;  // Redeclare for PHP < 8.4
    public readonly string $type;    // Redeclare for PHP < 8.4
    public readonly string $name;
}

$dto = FinalDto::make(name: 'Test', type: 'example');
$dto->status;  // 'active' (default inherited from BaseDto)
```

### Class Attribute Inheritance

Class-level attributes (`#[Casts]`, `#[With]`, `#[Validate]`, `#[Rules]`) also inherit from parent classes:

```php
use fab2s\Dt0\Attribute\Casts;
use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Attribute\Validate;

#[Casts(
    status: new Cast(default: 'pending'),
)]
#[Validate(BaseValidator::class)]
class BaseTaskDto extends Dt0
{
    public readonly string $title;
    public readonly string $status;
}

// Inherits Casts and Validate from BaseTaskDto
class PriorityTaskDto extends BaseTaskDto
{
    public readonly string $title;   // Redeclare for PHP < 8.4
    public readonly string $status;  // Redeclare for PHP < 8.4

    #[Cast(default: 0)]
    public readonly int $priority;
}

$task = PriorityTaskDto::make(title: 'Review PR');
$task->status;    // 'pending' (from inherited Casts)
$task->priority;  // 0 (from own Cast)
```

**Override class attributes** - Define the attribute on the child to override:

```php
#[Casts(
    status: new Cast(default: 'pending'),
)]
class BaseTaskDto extends Dt0
{
    public readonly string $status;
}

#[Casts(
    status: new Cast(default: 'urgent'),  // Override parent's default
)]
class UrgentTaskDto extends BaseTaskDto
{
    public readonly string $status;  // Redeclare for PHP < 8.4
}

$task = UrgentTaskDto::make();
$task->status;  // 'urgent'
```

## Validation

Dt0 provides validation architecture with a built-in standalone validator powered by Laravel's validation engine — no Laravel framework required:

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
    email: 'test@example.com',
    name: 'Jo',
    message: 'Hello!',
);
```

The standalone validator requires `illuminate/validation` and `illuminate/translation` (v11+):

```shell
composer require "illuminate/validation:^11.0|^12.0" "illuminate/translation:^11.0|^12.0"
```

**Rule priority**: When rules are defined at multiple levels, property-level `#[Rule]` takes precedence over class-level `#[Rules]`, which takes precedence over rules defined in `#[Validate]`.

**Laravel auto-detection**: When used inside a Laravel application, the `Validator` automatically delegates to Laravel's own validation factory — no configuration needed. Your DTOs work identically in both standalone and Laravel contexts.

The `ValidatorInterface` is open for custom implementations — bring your own validation engine if needed.

See [Validation Documentation](./docs/validation.md) for Laravel auto-detection details, locale configuration, custom translations, custom validators, and helper functions.

For full Laravel integration with model casting, see [Laravel Dt0](https://github.com/fab2s/laravel-dt0).

## Type System Integration

Dt0 works with PHP's type system, not against it. Casters attempt conversion and return `null` on failure. Your property types decide what's acceptable:

```php
class StrictDto extends Dt0
{
    public readonly string $required;   // null → TypeError
    public readonly ?string $optional;  // null → accepted
    public readonly int|string $flexible; // int or string accepted
}
```

This approach:
- Avoids duplicating validation logic
- Lets you declare acceptance criteria via types
- Produces clear errors from PHP itself

```php
$dto = StrictDto::make(
    required: null,  // TypeError: cannot be null
    // ...
);
```

## Extending Attributes

Dt0's attributes are extensible. Implement the appropriate interface or extend the abstract class:

| Attribute Type | Interface | Abstract Class |
|----------------|-----------|----------------|
| Class casts | [`CastsInterface`](./src/Attribute/CastsInterface.php) | [`CastsAbstract`](./src/Attribute/CastsAbstract.php) |
| Property cast | [`CastInterface`](./src/Attribute/CastInterface.php) | [`CastAbstract`](./src/Attribute/CastAbstract.php) |
| Validation | [`ValidateInterface`](./src/Attribute/ValidateInterface.php) | [`ValidateAbstract`](./src/Attribute/ValidateAbstract.php) |
| Class rules | [`RulesInterface`](./src/Attribute/RulesInterface.php) | [`RulesAbstract`](./src/Attribute/RulesAbstract.php) |
| Property rule | [`RuleInterface`](./src/Attribute/RuleInterface.php) | [`RuleAbstract`](./src/Attribute/RuleAbstract.php) |
| Output control | [`WithInterface`](./src/Attribute/WithInterface.php) | [`WithAbstract`](./src/Attribute/WithAbstract.php) |

**Access compiled property metadata:**

```php
$properties = MyDto::compile();          // Properties instance (cached)
$properties->toArray();                  // Property[] indexed by name
$property = $properties->get('fieldName'); // Single Property instance

// Inspect a property
$property->name;        // 'fieldName'
$property->types;       // Types instance with type information
$property->cast;        // The Cast attribute (or null)
$property->in;          // Input caster instance (or null)
$property->out;         // Output caster instance (or null)
$property->isDt0;       // true if property type is a Dt0
$property->isEnum;      // true if property type is an Enum
$property->hasDefault(); // true if a default value exists
$property->getDefault(); // The default value
```

## Performance

Dt0 compiles reflection and attribute metadata **once per class, per process**. The first instantiation of a Dt0 class triggers compilation; subsequent instantiations reuse the cached data with zero reflection overhead.

```php
// First call: reflection + attribute parsing
$user1 = UserDto::make(/* ... */);

// All subsequent calls: cached metadata, no reflection
$user2 = UserDto::make(/* ... */);
$user3 = UserDto::fromArray(/* ... */);
$user4 = UserDto::fromJson(/* ... */);
```

The cache is bounded by the number of Dt0 classes in your application, not by usage. If you have 20 Dt0 classes, you get 20 cache entries - regardless of how many instances you create.

### Benchmarks

Run the benchmark:

```shell
php benchmark/compare-spatie.php
```

#### Dt0 vs spatie/laravel-data (PHP 8.4, 10,000 iterations)

| Operation | Dt0 | spatie/laravel-data | Speedup |
|-----------|-----|---------------------|--------|
| Simple DTO (8 props, 5 casts) | 141.6 µs | 1,158 µs | **~8.2x faster** |
| Complex DTO (nested + arrays) | 741.9 µs | 3,628 µs | **~4.9x faster** |
| Round-trip (json→dto→json) | 248.4 µs | 2,004 µs | **~8.1x faster** |

**Repeated serialization (same instance):**

| Operation | Dt0 | spatie/laravel-data | Speedup |
|-----------|-----|---------------------|--------|
| toArray() (simple) | 3.6 µs | 679.4 µs | **~188.7x faster** |
| toArray() (nested) | 3.6 µs | 2,056 µs | **~571.1x faster** |
| toJson() | 2.8 µs | 681.8 µs | **~243.5x faster** |

The extreme serialization speedup (188-571x) applies when serializing the same instance multiple times - Dt0 caches the output structure on first call. Real-world scenarios where this matters:

- **API + logging**: serialize response, then log the same DTO
- **Event sourcing**: serialize for storage, broadcast, and audit trail
- **Queue jobs**: serialize for the queue, then again for monitoring
- **Caching layers**: serialize for Redis and for the HTTP response

For single-use serialization, expect ~10x improvement, consistent with hydration benchmarks.

## Exceptions

All Dt0 exceptions extend [`ContextException`](https://github.com/fab2s/ContextException), providing structured context for logging and debugging:

| Exception | Usage |
|-----------|-------|
| `Dt0Exception` | General DTO errors (missing properties, invalid input) |
| `CasterException` | Casting failures |
| `AttributeException` | Attribute configuration errors |

```php
try {
    $dto = UserDto::from($invalidInput);
} catch (Dt0Exception $e) {
    $e->getMessage();   // Human-readable message
    $e->getContext();   // Array with debugging information
}
```

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
# fix code style
composer fix

# run tests
composer test

# run tests with coverage
composer cov

# static analysis (src, level 9)
composer stan

# static analysis (tests, level 5)
composer stan-tests
```

## License

Dt0 is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
