# Output

Dt0 provides several output methods and flexible filtering to control which properties appear in the result.

## Table of Contents

- [Output Methods](#output-methods)
- [Output Filtering](#output-filtering)
  - [Adding Properties with `with()`](#adding-properties-with-with)
  - [Excluding Properties with `without()`](#excluding-properties-with-without)
  - [Selecting Properties with `only()`](#selecting-properties-with-only)
  - [Resetting Filters](#resetting-filters)

## Output Methods

```php
$dto->toArray();      // Array with objects intact
$dto->toJsonArray();  // Array with objects serialized (JsonSerializable called)
$dto->jsonSerialize();// Same as toJsonArray()
$dto->toJson();       // JSON string
$dto->toGz();         // Gzipped JSON string
json_encode($dto);    // JSON string (implements JsonSerializable)
(string) $dto;        // JSON string (implements Stringable)
```

Output casters (`out` / `both`) are applied during serialization. Results are cached per format — subsequent calls on the same instance return the cached value instantly.

## Output Filtering

Control which properties appear in output using `with()`, `without()`, and `only()`.

### Adding Properties with `with()`

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

### Excluding Properties with `without()`

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

### Selecting Properties with `only()`

```php
$user->only('id', 'name')->toArray();
// ['id' => 1, 'name' => 'John']
```

### Resetting Filters

```php
$user->clearWith();     // Remove all with() additions
$user->clearWithout();  // Remove all without() exclusions
```
