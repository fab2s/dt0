# Dt0
[![CI](https://github.com/fab2s/dt0/actions/workflows/ci.yml/badge.svg)](https://github.com/fab2s/dt0/actions/workflows/ci.yml) [![QA](https://github.com/fab2s/dt0/actions/workflows/qa.yml/badge.svg)](https://github.com/fab2s/dt0/actions/workflows/qa.yml) [![codecov](https://codecov.io/gh/fab2s/dt0/graph/badge.svg?token=VRX16UUB7Y)](https://codecov.io/gh/fab2s/dt0) [![Latest Stable Version](http://poser.pugx.org/fab2s/dt0/v)](https://packagist.org/packages/fab2s/dt0) [![Total Downloads](http://poser.pugx.org/fab2s/dt0/downloads)](https://packagist.org/packages/fab2s/dt0) [![Monthly Downloads](http://poser.pugx.org/fab2s/dt0/d/monthly)](https://packagist.org/packages/fab2s/dt0) [![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg?style=flat)](http://makeapullrequest.com) [![License](http://poser.pugx.org/fab2s/dt0/license)](https://packagist.org/packages/fab2s/dt0)

`Dt0` (_DeeTO_ or _DeTZerO_) is a DTO (_Data-Transport-Object_) PHP implementation than can both secure mutability and implement convenient ways to take control over input and output in various formats.

Either cast'ed or not, `Dt0` is able to hydrate any public property. Any class extending Dt0 will be compiled once per process for faster reuse (single reflexion and logic compilation).

`Dt0` achieves full immutability when it hydrates `readonly` properties. As a best practice, all of your `Dt0`'s _should_ only use `public readonly` properties as part of their public interfaces.

## Laravel

Laravel users may enjoy [Laravel Dt0](https://github.com/fab2s/laravel-dt0) adding proper supports for `Dt0`'s with Dt0 validation and model attribute casting.

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
$dt0 = SomeDt0::tryFrom($wannaBeDt0); // return null when nothing happens

/** @var Dt0 $dt0 */
$dto = SomeDt0::from($wannaBeDt0); // throws a Dt0Exception when nothing matched

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

`Dt0` has full support out of the box without any casting for [Enums](https://www.php.net/manual/en/language.types.enumerations.php) including [UnitEnum](https://www.php.net/manual/en/class.unitenum.php).

`Dt0` is as well aware of its inheritors without any casting. You can though find some usage for [Dt0Caster](./src/Caster/Dt0Caster.php) to handle more complex logic.

`Dt0` supports `in` and `out` casting. For example, you can cast any `DateTimeInterface` or `stringToTimeAble` strings to a Datetime property and have it output in Json format as a specific format :

````php
use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Caster\DateTimeCaster;
use fab2s\Dt0\Caster\DateTimeFormatCaster;
use fab2s\Dt0\Dt0;

class MyDto extends Dt0 {
    #[Cast(in: DateTimeCaster::class, out: new DateTimeFormatCaster(DateTimeFormatCaster::ISO))]
    public readonly DateTime $date;
}

/** @var Dt0 $dt0 */
$dt0 = MyDto::make(date:'1337-01-01 00:00:00');

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

`Dt0` [Casters](./src/Caster) also support for default values as well as input/output renaming:

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

`Dt0`'s can have a constructor with promoted props given they properly call their parent

`````php

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
`````

## Validation

`Dt0` comes with full validation logic but no specific implementation. For an implementation example, see [Laravel Dt0](https://github.com/fab2s/laravel-dt0)

## Requirements

`Dt0` is tested against php 8.1 and 8.2

## Contributing

Contributions are welcome, do not hesitate to open issues and submit pull requests.

## License

`Dt0` is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
