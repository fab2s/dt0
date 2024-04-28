# Dt0
[![CI](https://github.com/fab2s/dt0/actions/workflows/ci.yml/badge.svg)](https://github.com/fab2s/dt0/actions/workflows/ci.yml) [![QA](https://github.com/fab2s/dt0/actions/workflows/qa.yml/badge.svg)](https://github.com/fab2s/dt0/actions/workflows/qa.yml) [![codecov](https://codecov.io/gh/fab2s/dt0/graph/badge.svg?token=VRX16UUB7Y)](https://codecov.io/gh/fab2s/dt0) [![Latest Stable Version](http://poser.pugx.org/fab2s/dt0/v)](https://packagist.org/packages/fab2s/dt0) [![Total Downloads](http://poser.pugx.org/fab2s/dt0/downloads)](https://packagist.org/packages/fab2s/dt0) [![Monthly Downloads](http://poser.pugx.org/fab2s/dt0/d/monthly)](https://packagist.org/packages/fab2s/dt0) [![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg?style=flat)](http://makeapullrequest.com) [![License](http://poser.pugx.org/fab2s/dt0/license)](https://packagist.org/packages/fab2s/dt0)

`Dt0` (_DeeTO_ or _DeTZerO_) is a [DTO](https://en.wikipedia.org/wiki/Data_transfer_object) (_Data-Transport-Object_) PHP implementation that can both secure mutability and implement convenient ways to take control over input and output in various formats.

Any class extending `Dt0` will have its public properties, including `readonly` ones, hydrate-able from all formats supported: array, json string, and instances.

The logic behind the scene is compiled once per process for faster reuse (single reflexion and attribute logic compilation).

`Dt0` achieves full immutability when it hydrates `readonly` properties. As a best practice, all of your `Dt0`'s _should_ only use `public readonly` properties as part of their public interfaces.

## But why another DTO package

It is clear that there are many DTO packages available already, with some really good ones. But none of them (so far) made it to handle full immutability.

Mutable DTOs, with `writeable public properties`, kinda missed the purpose of providing with trust that no _accidental_ property update occurred and the peace of mind that comes with it.

It also seems to be a good practice to promote _some thinking_ by design when you would find yourself in the need to update a DTO in any way, instead of just allowing it in a way that just _seem_ to be ok with the implementation.

Some could argue that no one can prevent Dt0 swapping with new instances, but since you can track [object ids](https://www.php.net/manual/en/function.spl-object-id.php) when it matters, you can actually achieve complete integrity, being just impossible with other solutions.

Should the need for even more insurance arise, you can easily add a `public readonly property` to store a cryptographic hash based on input values to sign each of your `Dt0`s and use it to make sure that nothing wrong happened.

## Laravel

[Laravel](https://laravel.com/) users may enjoy [Laravel Dt0](https://github.com/fab2s/laravel-dt0) adding proper supports for `Dt0`'s with Dt0 validation and model attribute casting.

## Installation

`Dt0` can be installed using composer:

```shell
composer require "fab2s/dt0"
```

Once done, you can start playing :

```php

use fab2s\Dt0\Dt0;

// works if all public props have defaults
$dt0 = new SomeDt0;

// set at least props without default
$dt0 = new SomeDt0(readOnlyProp: $someValue /*, ... */); // <= argument order does not matter
                                                         // unless SomeDt0 has a constructor

// same as
$dt0 = SomeDt0::make(readOnlyProp: $someValue /*, ... */); // <= argument order never matter

$value = $dt0->readOnlyProp; // $someValue

/** @var array|string|SomeDt0|Dt0|null|mixed $wannaBeDt0 */
$dt0 = SomeDt0::tryFrom($wannaBeDt0); // return null when nothing works

/** @var Dt0 $dt0 */
$dto = SomeDt0::from($wannaBeDt0); // throws a Dt0Exception when nothing matched or more Throwable when something is too wrong

// keeps objects as such
$array = $dt0->toArray();

// toArray with call to jsonSerialize on implementing members
$jsonArray = $dt0->toJsonArray();
// same as 
$jsonArray = $dt0->jsonSerialize();

// toJson
$json = $dt0->toJson();
// same as 
$json = json_encode($dt0);

// will work if Dt0 has consistent in/out casters
// that is if caster out type is valid for input
$fromJson = SomeDt0::fromJson($json);
// same as
$fromJson = SomeDt0::fromString($json);
$fromJson->equals($dt0); // true

// always true
$dto->equals(SomeDt0::fromArray($dto->toArray())); 

// serializable
$serialized = serialize($dt0);
$unserialized = unserialize($serialized);
$unserialized->equal($dt0); // true

// Immutability with ...
$anotherInstance = $dto->clone();
$anotherInstance->equals($dto); // true

// ... updates :o
$updated = $dto->update(readOnlyProp: $anotherValue);
// or 
$updated = $dto->update(...['readOnlyProp' => $anotherValue]);
$updated->->equals($dto); // false
$updated->readOnlyProp; // $anotherValue

```

## Casting

`Dt0` comes with two `Attributes` to implement casting: [`Casts`](./src/Attribute/Casts.php) and [`Cast`](./src/Attribute/Cast.php)

`Cast` is used to define how to handle a property as a **property attribute** and `Casts` is used to set many `Cast` at once as a **class attribute**.

### Casts can be added in two ways:

- using the [`Casts`](./src/Attribute/Casts.php) **class attribute**:
    ````php
    use fab2s\Dt0\Attribute\Casts;
    use fab2s\Dt0\Attribute\Cast;
    use fab2s\Dt0\Dt0;
    
    #[Casts(
        new Cast(default: 'defaultFromCast', propName: 'prop1'),
        // same as 
        prop1: new Cast(default: 'defaultFromCast'),
        // ...
    )]
    class MyDt0 extends Dt0 {
        public readonly string $prop1;
    }
    ````

- using the [`Cast`](./src/Attribute/Cast.php) **property attribute**:
    ````php
    use fab2s\Dt0\Attribute\Casts;
    use fab2s\Dt0\Attribute\Cast;
    use fab2s\Dt0\Dt0;
    
    class MyDt0 extends Dt0 {
        #[Cast(default: 'defaultFromCast')]
        public readonly string $prop1;
    }
    ````

Combo of the above two are permitted as illustrated in [`DefaultDt0`](./tests/Artifacts/DefaultDt0.php).

> In case of redundancy, priority will be first in `Casts` then `Cast`.
> Dt0 has no opinion of the method used to define Casts. They will all perform the same as they are compiled once per process and kept ready for any reuse.

### Available Casters

`Dt0` comes with several [Casters](./src/Caster) ready to use. Writing your own is as easy as implementing the [`CasterInterface`](./src/Caster/CasterInterface.php)

They are documented in [**Casters Documentation**](./docs/casters.md)

### Usage

`Dt0` has full support out of the box without any `Caster` for [Enums](https://www.php.net/manual/en/language.types.enumerations.php) including [UnitEnum](https://www.php.net/manual/en/class.unitenum.php).

`Dt0` is as well aware of its inheritors without any casting. You can though find some usage for [`Dt0Caster`](./src/Caster/Dt0Caster.php) when property typing cannot be specific enough (read the target `Dt0` class).

`Dt0` supports `in` and `out` casting. For example, you can cast any `DateTimeInterface` or `stringToTimeAble` strings to a `Datetime` property and have it output in Json format in a specific format:

````php
use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Caster\DateTimeCaster;
use fab2s\Dt0\Caster\DateTimeFormatCaster;
use fab2s\Dt0\Dt0;

class MyDt0 extends Dt0 {
    #[Cast(in: DateTimeCaster::class, out: new DateTimeFormatCaster(DateTimeFormatCaster::ISO))]
    public readonly DateTime $date;
}

/** @var Dt0 $dt0 */
$dt0 = MyDt0::make(date:'1337-01-01 00:00:00');

$dt0->toArray();
/*
[
    'date' => DateTimeInstance,
] 
*/

$dt0->jsonSerialize();
/*
[
    'date' => '1337-01-01T00:00:00.000000Z',
]
*/

````

Every `Caster` will also support for default values as well as input/output renaming:

````php
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Attribute\Casts;

#[Casts(
    new Cast(default: 'defaultFromCast', propName: 'propClassCasted'),
    // same as 
    propClassCasted: new Cast(default: 'defaultFromCast'),
)]
class MyDt0 extends Dt0
{
    public readonly string $propClassCasted;

    #[Cast(default: null)]
    public readonly ?string $propCasted;
    
    #[Cast(renameFrom: 'inputName', renameTo: 'outputName', default: 'default')]
    public readonly string $propRenamed;
}

$dt0 = MyDt0::make();
$dt0->propClassCasted; // 'defaultFromCast'
$dt0->propCasted;      // 'null'
$dt0->propRenamed;     // 'default'


$dt0 = MyDt0::make(propCasted: 'Oh Yeah', inputName: "I don't exist"); // <= argument order never matter
$dt0->propRenamed; // "I don't exist"
$dt0->toArray();
/**
[
    'propClassCasted' => 'defaultFromCast',
    'propCasted'      => 'Oh Yeah',
    'propRenamed'     => "I don't exist",
] 
*/

// same as 
$dt0 = MyDt0::make(propCasted: 'Oh Yeah', outputName: "I don't exist"); 
$dt0->propRenamed; // "I don't exist"

// all renameTo are added to renameFrom
$dt0->equal(MyDt0::fromArray($dt0->toArray()); // true 

$dt0 = MyDt0::fromArray([ 
    'propCasted'      => 'Oh', // <= order never matter
    'propClassCasted' => 'Ho', 
]);
$dt0->propRenamed; // 'default'
$dt0->toArray();
/**
[
    'propClassCasted' => 'Oh',
    'propCasted'      => 'Ho',
    'propRenamed'     => 'default',
] 
*/

// output renaming only occurs in json format
$dt0->toJsonArray();
/**
[
    'propClassCasted' => 'Oh',
    'propCasted'      => 'Ho',
    'outputName'      => 'default',
] 
*/

````

The `Cast`'s `renameFrom` argument can also be an array to handle multiple incoming property names for a single internal property.

````php
    #[Cast(renameFrom: ['alias', 'legacy_name'])] // first in wins the race
    public readonly string $prop;
````

### Default values

`Casts` can carry a default value, even in the absence of hard property default (being impossible on readonly properties that are not promoted).

As php does not implement the `Nil` concept (_never set_ as opposed to being `null` or actually set to `null`), `Dt0` uses a null byte (`"\0"`) as default for `Caster->default` value in order to simplify usage. The alternative would be to require to set an extra boolean argument `hasDefault` to then set a default or to not allow `null` as an actual default value.

This implementation detail result in allowing `any` value except the `null byte` as a default property value from `Caster`.

Should you find yourself in the rather uncommon situation where you would actually want a `null byte` as a defaults property value, you would then need to either une a `non readonly` property with this hard default, but this would break immutability, or set this property as a promoted one in your constructor to preserve `readonly` and thus immutability of your `Dt0`.

All considered, this extra attention for a very particular case seems entirely neglectable compared to the burden of one extra argument in every other case.

## What about constructors

`Dt0`'s can have a constructor with promoted props given they properly call their parent:

````php

class ConstructedDt0 extends Dt0
{
    // un-casted
    public readonly string $stringNoCast;

    #[Cast(/*...*/)]
    public readonly ?string $stringCasted;

    public function __construct(
        public readonly string $promotedPropNoCast,
        #[Cast(/*...*/)]
        public readonly string $promotedPropCasted = 'default',
        // all constructor parameters, promoted on not, can be casted
        #[Cast(/*...*/)]
        ?string $myCustomVar = null,
        // Mandatory, the remaining $args will be used to further
        // initialize other public properties in this class
        ...$args,
    ) {
        // where the magic happens
        parent::__construct(...$args);
    }
}

// now you can
$dt0 = new ConstructedDt0(
    promotedPropNoCast: 'The order',
    promotedPropCasted: 'matters',
    myCustomVar: 'for constructor parameters',
    stringCasted: 'but not',
    stringNoCast: 'for regular props',
);

// ::make, ::fromArray, ::fromString, ::from ... don't care about argument orders
$dt0 = ConstructedDt0::make(
    stringCasted: 'The Order',
    stringNoCast: 'never',
    promotedPropNoCast: 'matter',
    promotedPropCasted: 'outside',
    myCustomVar: 'of the constructor',
);
````

## `make` and other static factory methods vs `new`

When dealing with `readonly` properties, there are of course some gotchas as they indeed can only be initialized once. If your `Dt0` uses a constructor with `public readonly` **promoted properties**, no Casting will be used when you create your `Dt0` instance with the `new` keyword as everything will be done before anything can happen.

On the other hand, using the `make` method will always work as expected with the full Casting capabilities of the package as in this case, all the magic will happen before the constructor is even called.

As a conclusion, it is always best practice to create your instances using any of the static factory method (`make`, `from`, `tryFrom`, `fromArray`, `fromString` and `fromJson`) which in the end is no big deal considering this can achieve **fully immutable DTOs** and the peace of mind that comes with it.

It does not mean that you should not use `public readonly` **promoted properties** as this is also the only way to provide with a hard default value for `public readonly` properties. It's just something to keep in mind when working with this package.

## Validation

`Dt0` comes with full validation logic but no specific implementation. For a fully functional implementation example, see [Laravel Dt0](https://github.com/fab2s/laravel-dt0)

## Exceptions

`Dt0`'s exception all extends [`ContextException`](https://github.com/fab2s/ContextException) and do carry contextual information that can be used in your exception logger if any.

## Requirements

`Dt0` is tested against php 8.1 and 8.2

## Contributing

Contributions are welcome, do not hesitate to open issues and submit pull requests.

## License

`Dt0` is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
