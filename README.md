# Dt0

`Dt0` (_DeeTO_ or _DeTZerO_) is a DTO (_Data-Transport-Object_) PHP implementation than can both secure mutability and implement convenient ways to take control over input and output in various format.

## Installation

`Dt0` can be installed using composer:

```shell
composer require "fab2s/dt0"
```

Once done, you can start playing :

```php

use fab2s\Dt0\Dt0;

// works if all public props have defaults
$dt0 = new SomeDt0();

// set at least props without default
$dt0 = new SomeDt0(readOnlyProp: $someValue /*, ... */); // <= argument order does not matter
                                                         // unless SomeDt0 has a constructor

// same as
$dt0 = SomeDt0::make(readOnlyProp: $someValue /*, ... */); // <= argument order never matter

$value = $dt0->readOnlyProp; // $someValue

/** @var array|string|SomeDt0|Dt0|null $wannaBeDt0 */
$dt0 = SomeDt0::tryFrom($wannaBeDt0);

/** @var Dt0 $dt0 */

// recursively applied among Dt0 members
$array = $dt0->toArray();

// toArray with call to jsonSerialize on compatible members
$jsonArray = $dt0->toJsonArray();
// same as 
$jsonArray = $dt0->jsonSerialize();

// toJson
$json = $dt0->toJson();
// same as 
$json = json_decode($dt0);

// serializable
$serialized = serialize($dt0);
$dt0 = unserialize($serialized);

```

## Casting

`Dt0` comes with two `Attributes` : `Casts` and `Cast`

`Cast` is used to define how to handle a property as a **property attribute** and `Casts` is used to set many `Cast` at once as a **class attribute**.

````php

use fab2s\Dt0\Dt0;

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
}

$dt0 = MyDt0::make();
/**
[
    'propClassCasted' => 'defaultFromCast',
    'propCasted' => null,
] 
*/

$dt0 = MyDt0::make(propCasted:'Oh Yeah');
/**
[
    'propClassCasted' => 'defaultFromCast',
    'propCasted' => 'Oh Yeah',
] 
*/

$dt0 = MyDt0::fromArray(['propCasted' => 'Oh', 'propClassCasted' => 'Ho']);
/**
[
    'propClassCasted' => 'Oh',
    'propCasted' => 'Ho',
] 
*/

````

## Requirements

`Dt0` is tested against php 8.1 and 8.2

## Contributing

Contributions are welcome, do not hesitate to open issues and submit pull requests.

## License

`Dt0` is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
