# Casters

Dt0 comes with several casters out of the box. All of them can be used as `in` (input) or `out` (output) with the `Cast` attribute, though some are more practical in one direction than the other.

## Table of Contents

- [Philosophy](#philosophy)
- [CasterInterface](#casterinterface)
- [Built-in Casters](#built-in-casters)
    - [ScalarCaster](#scalarcaster)
    - [JsonCaster](#jsoncaster)
    - [TrimCaster](#trimcaster)
    - [Base64Caster](#base64caster)
    - [ArrayOfCaster](#arrayofcaster)
    - [DateTimeCaster](#datetimecaster)
    - [CarbonCaster](#carboncaster)
    - [DateTimeFormatCaster](#datetimeformatcaster)
    - [MathCaster](#mathcaster)
    - [Dt0Caster](#dt0caster)
    - [ClassCaster](#classcaster)
    - [CasterCollection](#castercollection)
- [Creating Custom Casters](#creating-custom-casters)

## Philosophy

Casters follow a simple principle: **attempt conversion, return null on failure**. This delegates type enforcement to PHP's type system - your property types decide what's acceptable.

```php
#[Cast(in: new ScalarCaster(ScalarType::int))]
public readonly int $count;     // null → TypeError (enforced by PHP)

#[Cast(in: new ScalarCaster(ScalarType::int))]
public readonly ?int $count;    // null → accepted (you opted in)
```

The exception to this rule is `ArrayOfCaster` and `MathCaster`, which throw exceptions for invalid data because partial results would be misleading.

## CasterInterface

All casters implement [`CasterInterface`](../src/Caster/CasterInterface.php) and should extend [`CasterAbstract`](../src/Caster/CasterAbstract.php) for the base implementation:

```php
namespace fab2s\Dt0\Caster;

use fab2s\Dt0\Dt0;

interface CasterInterface extends HasDeclaringFqnInterface, HasPropNameInterface
{
    public function cast(mixed $value, array|Dt0|null $data = null): mixed;
}
```

### The `$data` Parameter

Most casters only need `$value`. The `$data` parameter is available for advanced scenarios where casting requires context beyond the single value:

| Direction | `$data` Type | Use Case |
|-----------|--------------|----------|
| Input (`in`) | `array` | Access other input fields to compute the value |
| Output (`out`) | `Dt0` | Access other properties to compute the output |

```php
public function cast(mixed $value, array|Dt0|null $data = null): mixed
{
    // Context information always available
    $this->getPropName();      // 'propertyName'
    $this->getDeclaringFqn();  // 'App\Dto\MyDto'

    if (is_array($data)) {
        // Input: $data is the full input array
        // Useful for combining multiple input fields
        return $data['firstName'] . ' ' . $data['lastName'];
    }

    if ($data instanceof Dt0) {
        // Output: $data is the Dt0 instance
        // Useful for computing derived values
        return $data->firstName . ' ' . $data->lastName;
    }

    return $value;
}
```

## Built-in Casters

### ScalarCaster

Casts values to scalar types (`int`, `float`, `bool`, `string`) using PHP's [`settype()`](https://www.php.net/manual/en/function.settype.php).

**Constructor:**
```php
new ScalarCaster(ScalarType|string $type)
```

**Behavior:**
- Non-scalar input → returns `null`
- `null` input → casts to the target type (e.g., `null` → `0` for int)

**Examples:**

```php
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Caster\ScalarCaster;
use fab2s\Dt0\Caster\ScalarType;

class StatsDto extends Dt0
{
    // Using ScalarType enum
    #[Cast(in: new ScalarCaster(ScalarType::int))]
    public readonly int $views;

    // Using string equivalent
    #[Cast(in: new ScalarCaster('float'))]
    public readonly float $rating;

    // Nullable - accepts null when cast fails
    #[Cast(in: new ScalarCaster(ScalarType::string))]
    public readonly ?string $description;

    // Boolean casting
    #[Cast(in: new ScalarCaster(ScalarType::bool))]
    public readonly bool $isActive;
}

$stats = StatsDto::make(
    views: '1000',      // string → int: 1000
    rating: '4.5',      // string → float: 4.5
    description: 123,   // int → string: '123'
    isActive: 1,        // int → bool: true
);
```

**Available ScalarTypes:**
- `ScalarType::int` / `'int'` / `'integer'`
- `ScalarType::float` / `'float'` / `'double'`
- `ScalarType::bool` / `'bool'` / `'boolean'`
- `ScalarType::string` / `'string'`


### JsonCaster

Bidirectional caster that decodes JSON strings on input and encodes to JSON strings on output. Detects direction automatically using the `$data` parameter context.

**Constructor:**
```php
new JsonCaster(
    bool $associative = true,
    int $flags = 0,
    int $depth = 512,
)
```

**Behavior:**
- Input (`$data` is array): JSON string → array/object
- Output (`$data` is Dt0): array/object → JSON string
- Invalid JSON on input → returns `null`
- Array passed on input → returned as-is (already decoded)

**Examples:**

```php
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Caster\JsonCaster;

class ConfigDto extends Dt0
{
    // JSON column from database
    #[Cast(in: JsonCaster::class, out: JsonCaster::class)]
    public readonly array $settings;

    // Decode as object instead of array
    #[Cast(in: new JsonCaster(associative: false))]
    public readonly object $metadata;
}

$config = ConfigDto::make(
    settings: '{"theme": "dark", "notifications": true}',
    metadata: '{"version": "1.0"}',
);

$config->settings;  // ['theme' => 'dark', 'notifications' => true]
$config->metadata;  // object with ->version = '1.0'

$config->toJsonArray();
// ['settings' => '{"theme":"dark","notifications":true}', ...]
```

### TrimCaster

Trims whitespace (or custom characters) from strings. Supports `trim`, `ltrim`, and `rtrim` modes.

**Constructor:**
```php
new TrimCaster(
    TrimType $trimType = TrimType::BOTH,
    ?string $characters = " \n\r\t\v\0",
)
```

**Behavior:**
- Non-string input → returns `null`
- String input → trimmed according to `TrimType`

**Examples:**

```php
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Caster\TrimCaster;
use fab2s\Dt0\Caster\TrimType;

class UserInputDto extends Dt0
{
    // Default: trim both sides
    #[Cast(in: TrimCaster::class)]
    public readonly string $name;

    // Trim only left side
    #[Cast(in: new TrimCaster(TrimType::LEFT))]
    public readonly string $code;

    // Trim only right side
    #[Cast(in: new TrimCaster(TrimType::RIGHT))]
    public readonly string $path;

    // Custom characters
    #[Cast(in: new TrimCaster(TrimType::BOTH, '/'))]
    public readonly string $slug;
}

$input = UserInputDto::make(
    name: '  John Doe  ',      // → 'John Doe'
    code: '  ABC123',          // → 'ABC123'
    path: '/var/log/',         // → '/var/log'
    slug: '///my-page///',     // → 'my-page'
);
```

**Available TrimTypes:**
- `TrimType::BOTH` - `trim()` (default)
- `TrimType::LEFT` - `ltrim()`
- `TrimType::RIGHT` - `rtrim()`

### Base64Caster

Bidirectional caster that decodes base64 on input and encodes to base64 on output. Useful for binary data in JSON payloads or database storage.

**Constructor:**
```php
new Base64Caster(bool $strict = true)
```

**Behavior:**
- Input (`$data` is array): base64 string → decoded string
- Output (`$data` is Dt0): string → base64 encoded string
- Invalid base64 with `strict: true` → returns `null`
- Non-string input → returns `null`

**Examples:**

```php
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Caster\Base64Caster;

class FileDto extends Dt0
{
    public readonly string $name;

    // Binary content stored as base64
    #[Cast(in: Base64Caster::class, out: Base64Caster::class)]
    public readonly string $content;
}

$file = FileDto::make(
    name: 'document.pdf',
    content: base64_encode($binaryContent),
);

$file->content;  // Decoded binary content

$file->toJsonArray();
// ['name' => 'document.pdf', 'content' => 'base64-encoded-string...']
```

```php
// Non-strict mode (lenient parsing)
class LegacyDto extends Dt0
{
    #[Cast(in: new Base64Caster(strict: false))]
    public readonly string $data;
}
```

### ArrayOfCaster

Casts an array where each element is transformed to the specified type. Supports scalars, Dt0 classes, and enums.

**Constructor:**
```php
new ArrayOfCaster(ScalarType|string $type)
```

**Behavior:**
- Non-iterable input → returns `null`
- Invalid element for scalar type → throws `CasterException`
- Invalid element for Dt0/Enum → element becomes `null` (uses `tryFrom`)

**Examples:**

```php
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Caster\ArrayOfCaster;
use fab2s\Dt0\Caster\ScalarType;

// Array of scalars
class IdsDto extends Dt0
{
    #[Cast(in: new ArrayOfCaster(ScalarType::int))]
    public readonly array $ids;
}

$dto = IdsDto::make(ids: ['1', '2', '3']);
$dto->ids; // [1, 2, 3] (all integers)
```

```php
// Array of Dt0 objects
class TagDto extends Dt0
{
    public readonly string $name;
    public readonly string $slug;
}

class ArticleDto extends Dt0
{
    public readonly string $title;

    #[Cast(in: new ArrayOfCaster(TagDto::class))]
    public readonly array $tags;
}

$article = ArticleDto::make(
    title: 'Hello World',
    tags: [
        ['name' => 'PHP', 'slug' => 'php'],
        ['name' => 'Dt0', 'slug' => 'dt0'],
        '{"name": "JSON", "slug": "json"}',  // JSON string works too
        TagDto::make(name: 'Instance', slug: 'instance'),  // Or existing instance
    ],
);

$article->tags[0]->name; // 'PHP'
$article->tags[2]->name; // 'JSON'
```

```php
// Array of enums
enum Priority: int
{
    case Low = 1;
    case Medium = 2;
    case High = 3;
}

class TaskListDto extends Dt0
{
    #[Cast(in: new ArrayOfCaster(Priority::class))]
    public readonly array $priorities;
}

$list = TaskListDto::make(priorities: [1, 2, 'High', Priority::Low]);
$list->priorities; // [Priority::Low, Priority::Medium, Priority::High, Priority::Low]
```

### DateTimeCaster

Parses various date representations into `DateTime` or `DateTimeImmutable` instances.

**Constructor:**
```php
new DateTimeCaster(
    DateTimeZone|string|null $timeZone = null,
    bool $immutable = true,
)
```

**Accepted inputs:**
- `DateTimeInterface` instances → converted to target class
- Strings → parsed via `strtotime()`
- Integers → treated as Unix timestamps
- Arrays → expects `['date' => '...', 'timezone' => '...']` format (JSON serialization format)

**Examples:**

```php
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Caster\DateTimeCaster;

class EventDto extends Dt0
{
    // Default: DateTimeImmutable, no timezone conversion
    #[Cast(in: DateTimeCaster::class)]
    public readonly DateTimeImmutable $startsAt;

    // Mutable DateTime
    #[Cast(in: new DateTimeCaster(immutable: false))]
    public readonly DateTime $endsAt;

    // Force specific timezone
    #[Cast(in: new DateTimeCaster('Europe/Paris'))]
    public readonly DateTimeImmutable $localTime;

    // Using DateTimeZone object
    #[Cast(in: new DateTimeCaster(new DateTimeZone('UTC')))]
    public readonly DateTimeImmutable $utcTime;
}

$event = EventDto::make(
    startsAt: '2024-06-15 09:00:00',           // String
    endsAt: 1718445600,                         // Unix timestamp
    localTime: new DateTime('now'),             // DateTime instance
    utcTime: ['date' => '2024-06-15 09:00:00'], // Array format
);
```

### CarbonCaster

Same as `DateTimeCaster` but returns `Carbon` or `CarbonImmutable` instances.

> **Requires:** `composer require nesbot/carbon`

**Constructor:**
```php
new CarbonCaster(
    DateTimeZone|string|null $timeZone = null,
    bool $immutable = true,
)
```

**Examples:**

```php
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Caster\CarbonCaster;
use Carbon\CarbonImmutable;
use Carbon\Carbon;

class ScheduleDto extends Dt0
{
    // Default: CarbonImmutable
    #[Cast(in: CarbonCaster::class)]
    public readonly CarbonImmutable $scheduledAt;

    // Mutable Carbon with timezone
    #[Cast(in: new CarbonCaster('America/New_York', false))]
    public readonly Carbon $localSchedule;
}

$schedule = ScheduleDto::make(
    scheduledAt: 'next monday 9am',
    localSchedule: '2024-06-15 14:00:00',
);

// Carbon's fluent API available
$schedule->scheduledAt->diffForHumans(); // "in 3 days"
```

### DateTimeFormatCaster

Formats a `DateTimeInterface` into a string. Primarily useful as an `out` caster for JSON serialization, but can also be used as an `in` caster to convert DateTime to string properties.

**Constructor:**
```php
new DateTimeFormatCaster(
    string $format,
    DateTimeZone|string|null $timeZone = null,
)
```

**Constants:**
- `DateTimeFormatCaster::ISO` = `'Y-m-d\TH:i:s.u\Z'` (ISO 8601 with microseconds)

**Examples:**

```php
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Caster\DateTimeCaster;
use fab2s\Dt0\Caster\DateTimeFormatCaster;

class ArticleDto extends Dt0
{
    public readonly string $title;

    // DateTime internally, ISO string in JSON output
    #[Cast(
        in: DateTimeCaster::class,
        out: new DateTimeFormatCaster(DateTimeFormatCaster::ISO),
    )]
    public readonly DateTimeImmutable $publishedAt;

    // DateTime internally, custom format in JSON output
    #[Cast(
        in: DateTimeCaster::class,
        out: new DateTimeFormatCaster('Y-m-d'),
    )]
    public readonly DateTimeImmutable $date;

    // Store as string property (using as 'in' caster)
    #[Cast(in: new DateTimeFormatCaster('Y-m-d H:i:s'))]
    public readonly string $formattedDate;
}

$article = ArticleDto::make(
    title: 'Hello',
    publishedAt: '2024-06-15 09:30:00',
    date: new DateTime('2024-06-15'),
    formattedDate: new DateTime('2024-06-15 09:30:00'),
);

$article->publishedAt;    // DateTimeImmutable instance
$article->formattedDate;  // '2024-06-15 09:30:00' (string)

$article->jsonSerialize();
// [
//     'title' => 'Hello',
//     'publishedAt' => '2024-06-15T09:30:00.000000Z',
//     'date' => '2024-06-15',
//     'formattedDate' => '2024-06-15 09:30:00',
// ]
```

### MathCaster

Casts numeric values to [`Math`](https://github.com/fab2s/Math) instances for high-precision decimal calculations.

> **Requires:** `composer require fab2s/math`

**Constructor:**
```php
new MathCaster(?int $precision = null)
```

**Behavior:**
- Unlike other casters, throws `InvalidArgumentException` for non-numeric input, unless default is set.
- This is intentional: for financial calculations, silent `null` would be dangerous

**Examples:**

```php
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Caster\MathCaster;
use fab2s\Math\Math;

class InvoiceDto extends Dt0
{
    // Default precision (from Math configuration)
    #[Cast(in: MathCaster::class)]
    public readonly Math $subtotal;

    // Fixed 2 decimal precision (for currency)
    #[Cast(in: new MathCaster(2))]
    public readonly Math $total;

    // High precision for rates
    #[Cast(in: new MathCaster(8))]
    public readonly Math $exchangeRate;
}

$invoice = InvoiceDto::make(
    subtotal: '1234.56',
    total: 1500.999,        // Will be handled with 2 decimal precision
    exchangeRate: '0.85432198',
);

// Math operations available
$invoice->subtotal->add($invoice->total)->mul($invoice->exchangeRate);
```

### Dt0Caster

Explicitly casts values to a specific Dt0 class. While Dt0 automatically handles typed Dt0 properties, this caster is useful when:
- The property type is generic (`object`, `mixed`)
- You want to output a Dt0 from a string/array property

**Constructor:**
```php
new Dt0Caster(string $fqn)
```

**Examples:**

```php
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Caster\Dt0Caster;

class AddressDto extends Dt0
{
    public readonly string $street;
    public readonly string $city;
}

class PersonDto extends Dt0
{
    public readonly string $name;

    // When property can't be specifically typed
    #[Cast(in: new Dt0Caster(AddressDto::class))]
    public readonly object $address;

    // Store as JSON string, output as Dt0
    #[Cast(out: new Dt0Caster(AddressDto::class))]
    public readonly string $addressJson;

    // Store as array, output as Dt0
    #[Cast(out: new Dt0Caster(AddressDto::class))]
    public readonly array $addressData;
}

$person = PersonDto::make(
    name: 'John',
    address: ['street' => '123 Main St', 'city' => 'Boston'],
    addressJson: '{"street": "456 Oak Ave", "city": "NYC"}',
    addressData: ['street' => '789 Pine Rd', 'city' => 'LA'],
);

$person->address;      // AddressDto instance
$person->addressJson;  // '{"street": "456 Oak Ave", "city": "NYC"}' (string)
$person->addressData;  // ['street' => '789 Pine Rd', 'city' => 'LA'] (array)

$person->jsonSerialize();
// addressJson and addressData become AddressDto instances in output
```

### ClassCaster

Instantiates arbitrary classes from input values. Useful for value objects or classes that aren't Dt0s.

**Constructor:**
```php
new ClassCaster(
    ?string $fqn = null,
    mixed ...$parameters,  // Default constructor parameters
)
```

**Behavior:**
- If value is already an instance of the class → returned as-is
- If value is an array → spread as constructor arguments
- If value is scalar → passed as single constructor argument
- Otherwise → uses default `$parameters`

**Examples:**

```php
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Caster\ClassCaster;

// A simple value object (not a Dt0)
class Money
{
    public function __construct(
        public readonly int $amount,
        public readonly string $currency = 'USD',
    ) {}
}

class Email
{
    public function __construct(public readonly string $address) {}
}

class OrderDto extends Dt0
{
    // From array: spreads as constructor args
    #[Cast(in: new ClassCaster(Money::class))]
    public readonly Money $total;

    // From scalar: single constructor arg
    #[Cast(in: new ClassCaster(Email::class))]
    public readonly Email $customerEmail;

    // With default parameters
    #[Cast(in: new ClassCaster(Money::class, 0, 'EUR'))]
    public readonly Money $discount;
}

$order = OrderDto::make(
    total: ['amount' => 9999, 'currency' => 'USD'],  // Array spread
    customerEmail: 'john@example.com',                // Scalar
    discount: ['amount' => 500],                      // Partial array (currency from default? No - uses array)
);

$order->total->amount;        // 9999
$order->total->currency;      // 'USD'
$order->customerEmail->address; // 'john@example.com'
```

### CasterCollection

Chains multiple casters into a pipeline where each caster's output becomes the next caster's input.

**Constructor:**
```php
new CasterCollection(CasterInterface|string ...$casters)
```

**Examples:**

```php
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Caster\CasterCollection;
use fab2s\Dt0\Caster\ScalarCaster;
use fab2s\Dt0\Caster\ScalarType;

class DataDto extends Dt0
{
    // Cast to int, then to string: "42" → 42 → "42"
    #[Cast(in: new CasterCollection(
        new ScalarCaster(ScalarType::int),
        new ScalarCaster(ScalarType::string),
    ))]
    public readonly string $numericString;

    // Can pass class strings instead of instances
    #[Cast(in: new CasterCollection(
        ScalarCaster::class,  // Will be instantiated (needs parameterless constructor)
        // ...
    ))]
    public readonly mixed $value;
}
```

**Practical use case - sanitization pipeline:**

```php
class SanitizingCaster extends CasterAbstract
{
    public function cast(mixed $value, array|Dt0|null $data = null): ?string
    {
        return is_string($value) ? trim(strip_tags($value)) : null;
    }
}

class TruncatingCaster extends CasterAbstract
{
    public function __construct(public readonly int $maxLength = 255) {}

    public function cast(mixed $value, array|Dt0|null $data = null): ?string
    {
        return is_string($value) ? substr($value, 0, $this->maxLength) : null;
    }
}

class CommentDto extends Dt0
{
    #[Cast(in: new CasterCollection(
        new SanitizingCaster,
        new TruncatingCaster(1000),
    ))]
    public readonly string $content;
}
```

## Creating Custom Casters

Extend `CasterAbstract` and implement the `cast()` method:

```php
use fab2s\Dt0\Caster\CasterAbstract;
use fab2s\Dt0\Dt0;

class SlugCaster extends CasterAbstract
{
    public function cast(mixed $value, array|Dt0|null $data = null): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        return strtolower(
            preg_replace('/[^a-z0-9]+/i', '-', trim($value))
        );
    }
}

// Usage
class ArticleDto extends Dt0
{
    public readonly string $title;

    #[Cast(in: SlugCaster::class)]
    public readonly string $slug;
}

$article = ArticleDto::make(
    title: 'Hello World',
    slug: 'Hello World!!!',
);
$article->slug; // 'hello-world'
```

### Casters with Configuration

Add constructor parameters for configurable behavior:

```php
class PrefixCaster extends CasterAbstract
{
    public function __construct(
        public readonly string $prefix,
        public readonly string $separator = '_',
    ) {}

    public function cast(mixed $value, array|Dt0|null $data = null): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        return $this->prefix . $this->separator . $value;
    }
}

class OrderDto extends Dt0
{
    #[Cast(in: new PrefixCaster('ORD', '-'))]
    public readonly string $orderId;
}

$order = OrderDto::make(orderId: '12345');
$order->orderId; // 'ORD-12345'
```

### Context-Aware Casters

Use the `$data` parameter for casters that need multiple values:

```php
class FullNameCaster extends CasterAbstract
{
    public function cast(mixed $value, array|Dt0|null $data = null): ?string
    {
        // Input: combine from array fields
        if (is_array($data)) {
            $first = $data['firstName'] ?? '';
            $last = $data['lastName'] ?? '';
            return trim("$first $last") ?: null;
        }

        // Output: combine from Dt0 properties
        if ($data instanceof Dt0) {
            return trim("$data->firstName $data->lastName") ?: null;
        }

        return $value;
    }
}

class PersonDto extends Dt0
{
    public readonly string $firstName;
    public readonly string $lastName;

    #[Cast(in: new FullNameCaster, out: new FullNameCaster)]
    public readonly string $fullName;
}
```

### Bidirectional Casters

Some casters work in both directions by detecting context via the `$data` parameter. Built-in examples include `JsonCaster` and `Base64Caster`. See their documentation above for details.

To create your own bidirectional caster:

```php
class EncryptedCaster extends CasterAbstract
{
    public function __construct(
        private readonly string $key,
    ) {}

    public function cast(mixed $value, array|Dt0|null $data = null): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        // Output: encrypt
        if ($data instanceof Dt0) {
            return $this->encrypt($value);
        }

        // Input: decrypt
        return $this->decrypt($value);
    }

    private function encrypt(string $value): string { /* ... */ }
    private function decrypt(string $value): string { /* ... */ }
}
```
