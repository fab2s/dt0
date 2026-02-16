# Extending Dt0

Dt0's attributes and validation are extensible. Implement the appropriate interface or extend the abstract class to create custom behavior.

## Table of Contents

- [Extending Attributes](#extending-attributes)
- [Compiled Property Metadata](#compiled-property-metadata)
- [Type System Integration](#type-system-integration)

## Extending Attributes

| Attribute Type | Interface | Abstract Class |
|----------------|-----------|----------------|
| Class casts | [`CastsInterface`](../src/Attribute/CastsInterface.php) | [`CastsAbstract`](../src/Attribute/CastsAbstract.php) |
| Property cast | [`CastInterface`](../src/Attribute/CastInterface.php) | [`CastAbstract`](../src/Attribute/CastAbstract.php) |
| Validation | [`ValidateInterface`](../src/Attribute/ValidateInterface.php) | [`ValidateAbstract`](../src/Attribute/ValidateAbstract.php) |
| Class rules | [`RulesInterface`](../src/Attribute/RulesInterface.php) | [`RulesAbstract`](../src/Attribute/RulesAbstract.php) |
| Property rule | [`RuleInterface`](../src/Attribute/RuleInterface.php) | [`RuleAbstract`](../src/Attribute/RuleAbstract.php) |
| Output control | [`WithInterface`](../src/Attribute/WithInterface.php) | [`WithAbstract`](../src/Attribute/WithAbstract.php) |

## Compiled Property Metadata

Access the compiled metadata for any Dt0 class:

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

## Type System Integration

Dt0 works with PHP's type system, not against it. Casters attempt conversion and return `null` on failure. Your property types decide what's acceptable:

```php
class StrictDto extends Dt0
{
    public readonly string $required;   // null -> TypeError
    public readonly ?string $optional;  // null -> accepted
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
