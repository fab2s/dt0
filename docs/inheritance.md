# Attribute Inheritance

Dt0 resolves attributes up the parent class chain — both property-level and class-level. This enables powerful patterns for code reuse without repeating attribute definitions.

## Table of Contents

- [Property Attribute Inheritance](#property-attribute-inheritance)
- [Class Attribute Inheritance](#class-attribute-inheritance)
- [PHP 8.4 Note](#php-84-note)

## Property Attribute Inheritance

When a property doesn't have an attribute, Dt0 walks up the parent class chain looking for the same property with that attribute:

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

### Overriding Inherited Attributes

Child classes can override parent attributes by defining their own:

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

### Multi-Level Inheritance

Attributes are resolved up the entire chain:

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

## Class Attribute Inheritance

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

### Overriding Class Attributes

Define the attribute on the child to override:

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

## PHP 8.4 Note

PHP 8.4 introduces readonly property inheritance, which makes attribute inheritance cleaner. You no longer need to redeclare parent properties in child classes — they're inherited automatically along with their attributes.

The examples in this document show property redeclaration for compatibility with PHP 8.1-8.3. On PHP 8.4+, you can omit the redeclarations.
