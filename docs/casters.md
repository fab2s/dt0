# Casters

`Dt0` comes with several `Caster` out of the box. All of them can be used as `in` or `out` with the `Cast` **Attribute** but they won't all have the same practical usage in both direction. IT will all depend upon your needs.

The general approach is that Casters that do return a single value, as opposed to an array or collection, may return null instead of throwing an exception as it will be up to the Dt0 using them to decide to either to accept `null` or not as a value for their properties.

## ScalarCaster

[`ScalarCaster`](./../src/Caster/ScalarCaster.php) will cast any scalar, including `null`, to the desired type.

Supported types are any scalar type, either set as string or as a [`ScalarType`](./../src/Caster/ScalarType.php) enum.

Casting is performed internally using [settype()](https://www.php.net/manual/en/function.settype.php)

Usage:

````php
    #[Cast(in: new ScalarCaster(ScalarType::int))] // any ScalarType enum or string equivalent
    public readonly int $prop1; // will throw if Caster result is null
    #[Cast(in: new ScalarCaster(ScalarType::string))] // any ScalarType enum or string equivalent
    public readonly ?string $prop2; // will be null in case the initializing value is not scalar or cast-able to string
````

## ArrayOfCaster

[`ArrayOfCaster`](./../src/Caster/ArrayOfCaster.php) will turn any array into an array of the supported types.

Supported types are any scalar type, either set as string or as a [`ScalarType`](./../src/Caster/ScalarType.php) enum, as well as any Dt0 and enum (backed or not).

`ArrayOfCaster` will throw a `CasterException` or a `Dt0Exception` if any of the input array member cannot be cast'ed to the desired type.

````php
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Caster\ArrayOfCaster;

class AnotherDt0 extends Dt0 {
    public readonly string $name;
}


class MyDt0 extends Dt0 {
    #[Cast(in: new ArrayOfCaster(AnotherDt0::class))] // Dt0|UnitEnum|ScalarType|string
    public readonly array $prop;
}

$dt0 = MyDt0::make(prop: [
    /* any type that can be tried as a AnotherDt0, json, array or AnotherDt0 instance*/
    '{"name":"John"}',
    ['name' => 'Doe'],
    AnotherDt0::make(name: 'Jane'),
]);

$dt0->prop[0]->name; // John
$dt0->prop[1]->name; // Doe
$dt0->prop[2]->name; // Jane
````

## DateTimeCaster

[`DateTimeCaster`](./../src/Caster/DateTimeCaster.php) will cast any [strtotime](https://www.php.net/manual/en/function.strtotime)-able string, `DateTimeInterface` instance, or JSON/array representation or a date into a `DateTimeImmutable` (default) or `DateTime` instance with the desired timezone.

Usage:

````php
    #[Cast(in: DateTimeCaster::class)]
    public readonly DateTimeImmutable $prop1; // DateTimeImmutable instance
    #[Cast(in: new DateTimeCaster('Antarctica/Troll', false))]
    public readonly ?DateTime $prop2; // DateTime instance Antarctica/Troll as a timezone
````

## CarbonCaster

[`CarbonCaster`](./../src/Caster/CarbonCaster.php) will do just as `DateTimeCaster` but will return a `CarbonImmutable` (default) or a `Carbon` instance.

No need to say that you will need to [`require`](https://getcomposer.org/doc/03-cli.md#require-r) [`nesbot/carbon`](https://carbon.nesbot.com/) in your project before you can use this one.

Usage:

````php
    #[Cast(in: DateTimeCaster::class)]
    public readonly CarbonImmutable $prop1; // CarbonImmutable date instance
    #[Cast(in: new DateTimeCaster(new DateTimeZone('Antarctica/Troll'), false))]
    public readonly ?Carbon $prop2; // Carbon date instance with Antarctica/Troll as a timezone
````

## DateTimeFormatCaster

[`DateTimeFormatCaster`](./../src/Caster/DateTimeFormatCaster.php) will cast any `DateTimeInterface` into the desired string format.

`DateTimeFormatCaster` can be handy to use to have a controlled string format in JSON output of `Dt0`'s but can as well be used to transform `DateTimeInterface` instance into string properties.

Usage:

````php
    #[Cast(in: DateTimeCaster::class, out: new DateTimeFormatCaster(DateTimeFormatCaster::ISO)]
    public readonly CarbonImmutable $date; // DateTimeImmutable date instance internally and 'Y-m-d\TH:i:s.u\Z' format in json output
    #[Cast(in: new DateTimeFormatCaster('Y-m-d')]
    public readonly string $dateString;

````

## MathCaster

[`MathCaster`](./../src/Caster/MathCaster.php) will turn any number (`int|float|string`) into a `Math` instance.

No need to say that you will need to [`require`](https://getcomposer.org/doc/03-cli.md#require-r) [`fab2s/Math`](https://github.com/fab2s/Math) in your project before you can use this one.

> Math is a Bcmath based math helper for high precision base 10 decimal calculus

As the whole purpose of `Math` is to manipulate decimal number with a high level of confidence (read money), it will throw an `InvalidArgumentException` whenever input data is nothing like a number instead of returning `null`.

Usage:

````php
    #[Cast(in: MathCaster::class)] // or new MathCaster(int) to additionally set precision to int decimals
    public readonly Math $number;
````

## Dt0Caster

While `Dt0` is able to handle `Dt0` typed property out of the box, [`Dt0Caster`](./../src/Caster/Dt0Caster.php) can be usefully when the class property cannot be properly typed or to output Dt0's from array / string properties

Usage:

````php
    #[Cast(in: new Dt0Caster(MyDt0::class))]
    public readonly object $myDt0;
    #[Cast(out: new Dt0Caster(MyDt0::class))]
    public readonly string $myDt0Json; // will be a MyDt0 instance in the carrying Dt0 toJsonArray / jsonSerialize output
    #[Cast(out: new Dt0Caster(MyDt0::class))]
    public readonly array $myDt0props; // will be a MyDt0 instance in the carrying Dt0 toJsonArray / jsonSerialize output
````