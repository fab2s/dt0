# Creating Instances

Dt0 provides multiple ways to create instances, from simple factory methods to custom constructors with promoted properties.

## Table of Contents

- [Factory Methods](#factory-methods)
- [Using Constructors](#using-constructors)
- [`new` vs Factory Methods](#new-vs-factory-methods)

## Factory Methods

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

## Using Constructors

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

## `new` vs Factory Methods

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

// Casting won't apply to $date (has default)
$event = new EventDto(date: '2024-01-15', endDate: new DateTime());  // TypeError for $date

// Casting works for $endDate (no default)
$event = new EventDto(endDate: '2024-01-15');  // Works, $date uses its default

// Factory methods always work - casting applies to all properties
$event = EventDto::make(date: '2024-01-15', endDate: '2024-01-16');  // Both cast correctly
```

**Best practice**: Use factory methods (`make`, `from`, `fromArray`, etc.) for full casting support. Reserve `new` for cases where you're passing already-correct types or relying on defaults.
