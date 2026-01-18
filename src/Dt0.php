<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0;

use ArrayAccess;
use ArrayIterator;
use Closure;
use fab2s\Dt0\Attribute\WithProp;
use fab2s\Dt0\Attribute\WithPropInterface;
use fab2s\Dt0\Exception\Dt0Exception;
use fab2s\Dt0\Property\Properties;
use fab2s\Dt0\Property\Property;
use IteratorAggregate;
use JsonException;
use JsonSerializable;
use ReflectionException;
use Stringable;
use Throwable;
use Traversable;
use UnitEnum;

abstract class Dt0 implements ArrayAccess, IteratorAggregate, JsonSerializable, Stringable
{
    public const DT0_NIL = "\0";
    protected Properties $dt0Properties;
    protected array $dt0Output = [];

    /**
     * @var array<key-of<$this>, string>
     */
    protected array $dt0Without = [];

    /**
     * @var array<literal-string, Closure(static):mixed|callable-string<static>>
     */
    protected array $dt0With         = [];
    protected static array $dt0Cache = [];

    /**
     * @throws Dt0Exception
     * @throws ReflectionException
     */
    public function __construct(mixed ...$args)
    {
        $this->dt0Properties = static::compile();
        $args                = static::initializeRenameFrom($this->dt0Properties, $args);
        foreach ($this->dt0Properties->toArray() as $name => $property) {
            if (! $property->property->isInitialized($this)) {
                if (static::initializeValue($this->dt0Properties, $property, $args, $value)) {
                    $property->property->setValue($this, $property->cast($value, $args));
                } else {
                    throw (new Dt0Exception("Missing required property $name in " . static::class))
                        ->setContext([
                            'input' => $args,
                        ])
                    ;
                }
            }
        }

        if ($this->dt0Properties?->with) {
            foreach ($this->dt0Properties->with->getWiths() as $name => $option) {
                $this->with($name, $option);
            }
        }
    }

    /**
     * @throws Dt0Exception
     * @throws ReflectionException
     */
    public static function make(mixed ...$args): static
    {
        $properties = static::compile();
        $args       = static::initializeRenameFrom($properties, $args);

        foreach ($properties->earlyInits() as $name => $property) {
            if ($property->needEarlyCast) {
                if (static::initializeValue($properties, $property, $args, $value)) {
                    $args[$name] = $property->cast($value, $args);
                }

                continue;
            }

            if (
                $property->needEarlyDefault
                && ! array_key_exists($property->name, $args)
            ) {
                $args[$name] = $property->getDefault();
            }
        }

        if ($properties->constructorParameters) {
            $args = [
                ...array_intersect_key($args, $properties->constructorParameters),
                ...array_diff_key($args, $properties->constructorParameters),
            ];
        }

        return new static(...$args);
    }

    /**
     * @throws Dt0Exception|ReflectionException
     */
    public static function withValidation(mixed ...$args): static
    {
        $properties = static::compile();
        $args       = static::initializeRenameFrom($properties, $args);
        if ($properties->validator) {
            return static::make(...$properties->validator->validate($args));
        }

        throw new Dt0Exception('Cannot validate without a validator');
    }

    /**
     * @param WithProp|Closure(static):mixed|bool|callable-string<static>|null $getter
     *
     * @return $this
     */
    public function with(string $name, WithProp|Closure|string|bool|null $getter = null): static
    {
        $this->dt0With[$name] = $this->getWithClosure($name, $getter);

        unset($this->dt0Without[$name]);

        return $this->clearOutputCache();
    }

    public function getWithClosure(string $name, WithPropInterface|Closure|string|bool|null $getter = null): Closure
    {
        if ($getter instanceof WithPropInterface) {
            $name = $getter->name ?? $name;

            $getter = $getter->getter ?? null;
        }

        return match (true) {
            ! is_object($getter) => (function () use ($name, $getter) {
                $method = $getter === null || $getter === false ? $name : ($getter === true ? 'get' . ucfirst($name) : $getter);

                return match (true) {
                    $name === $method => function () use ($name) {
                        return $this->{$name};
                    },
                    default => function () use ($method) {
                        return $this->{$method}();
                    },
                };
            })(),
            default => function () use ($getter) {
                return $getter($this);
            },
        };
    }

    public function without(string ...$names): static
    {
        $remove           = array_combine($names, $names);
        $this->dt0Without = array_replace($this->dt0Without, $remove);
        $this->dt0With    = array_diff_key($this->dt0With, $remove);

        return $this->clearOutputCache();
    }

    public function clearWith(): static
    {
        $this->dt0Without = array_replace($this->dt0Without, $this->dt0With);
        $this->dt0With    = [];

        return $this->clearOutputCache();
    }

    public function clearWithout(): static
    {
        $this->dt0Without = [];

        return $this->clearOutputCache();
    }

    public function only(string ...$names): static
    {
        $only             = array_combine($names, $names);
        $this->dt0Without = array_replace($this->dt0Without, array_diff_key($this->dt0Properties->toNames(), $only));
        $this->dt0With    = array_intersect_key($this->dt0With, $only);

        return $this->clearOutputCache();
    }

    /**
     * @throws Dt0Exception|ReflectionException
     */
    public function clone(): static
    {
        return static::fromArray($this->toArray());
    }

    /**
     * @throws Dt0Exception|ReflectionException
     */
    public function update(mixed ...$update): static
    {
        return static::fromArray(array_replace($this->toArray(), $update));
    }

    /**
     * @throws JsonException
     */
    public function equal(self $dt0): bool
    {
        return $this->toJson() === $dt0->toJson();
    }

    public function toArray(): array
    {
        if (isset($this->dt0Output[Format::ARRAY->value])) {
            return array_diff_key($this->dt0Output[Format::ARRAY->value], $this->dt0Without);
        }

        $result = [];
        $withs  = $this->dt0With;
        foreach ($this->dt0Properties->toArray() as $name => $property) {
            if (isset($this->dt0Without[$name])) {
                continue;
            }

            if (isset($withs[$name])) {
                $result[$name] = ($withs[$name])();
                unset($withs[$name]);

                continue;
            }

            $key          = $this->dt0Properties->getToName($name);
            $result[$key] = $property?->out ? $property->out->cast($this->$name, $this) : $this->$name;

            if ($key !== $name) {
                unset($result[$name]);
            }
        }

        // left with extra props withs
        foreach ($withs as $nonPropName => $getter) {
            $result[$nonPropName] = $getter();
        }

        return array_diff_key($this->dt0Output[Format::ARRAY->value] = $result, $this->dt0Without);
    }

    public function jsonSerialize(): array
    {
        return $this->dt0Output[Format::JSON_SERIALISED->value] ??= array_map(fn ($value) => static::jsonSerializeValue($value), $this->toArray());
    }

    public static function jsonSerializeValue(mixed $value): mixed
    {
        return match (true) {
            $value instanceof JsonSerializable => $value->jsonSerialize(),
            $value instanceof UnitEnum         => $value->value ?? $value->name,
            default                            => $value,
        };
    }

    /**
     * @throws JsonException
     */
    public function toJson(int $flags = JSON_THROW_ON_ERROR &JSON_PRESERVE_ZERO_FRACTION, int $depth = 512): string
    {
        return $this->dt0Output[Format::JSON->value] ??= Json::encode($this, $flags, $depth);
    }

    /**
     * @throws JsonException
     */
    public function toGz(int $flags = JSON_THROW_ON_ERROR &JSON_PRESERVE_ZERO_FRACTION, int $depth = 512): string
    {
        return $this->dt0Output[Format::JSON->value] ??= Json::gzEncode($this, $flags, $depth);
    }

    /**
     * @throws JsonException
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @throws JsonException
     */
    public function toString(): string
    {
        return $this->toJson();
    }

    public function toJsonArray(): array
    {
        return $this->jsonSerialize();
    }

    /**
     * @throws Dt0Exception|ReflectionException
     */
    public static function fromArray(array $input): static
    {
        return static::make(...$input);
    }

    /**
     * @throws JsonException|Dt0Exception|ReflectionException
     */
    public static function fromJson(string $json, int $flags = JSON_THROW_ON_ERROR &JSON_PRESERVE_ZERO_FRACTION, int $depth = 512): static
    {
        return static::make(...Json::decode($json, true, $flags, $depth));
    }

    /**
     * @return $this
     *
     * @throws Dt0Exception
     * @throws JsonException
     * @throws ReflectionException
     */
    public static function fromGz(string $gz, int $flags = JSON_THROW_ON_ERROR &JSON_PRESERVE_ZERO_FRACTION, int $depth = 512): static
    {
        return static::make(...Json::gzDecode($gz, true, $flags, $depth));
    }

    /**
     * @throws JsonException|Dt0Exception|ReflectionException
     */
    public static function fromString(string $string): static
    {
        return static::fromJson($string);
    }

    public static function tryFrom(mixed $input): ?static
    {
        try {
            return static::from($input);
        } catch (Throwable $t) {
            return null;
        }
    }

    /**
     * @throws JsonException|Dt0Exception|ReflectionException
     */
    public static function from(mixed $input): ?static
    {
        return match (true) {
            $input instanceof static => $input,
            is_string($input)        => static::fromString($input),
            is_array($input)         => static::fromArray($input),
            default                  => throw (new Dt0Exception('Failed to initialize ' . static::class))
                ->setContext([
                    'input' => $input,
                ]),
        };
    }

    protected static function initializeValue(Properties $properties, Property $property, array $input, mixed &$value = null): bool
    {
        if (array_key_exists($property->name, $input)) {
            $value = $input[$property->name];

            return true;
        }

        if ($property->hasDefault()) {
            $value = $property->getDefault();

            return true;
        }

        return false;
    }

    protected static function initializeRenameFrom(Properties $properties, array $parameters): array
    {
        foreach ($properties->getRenameFrom() as $from => $to) {
            if (
                ! array_key_exists($to, $parameters)
                && array_key_exists($from, $parameters)
            ) {
                $parameters[$to] = $parameters[$from];
            }
        }

        return $parameters;
    }

    /**
     * @throws ReflectionException
     */
    public static function compile(): Properties
    {
        if (isset(self::$dt0Cache[static::class])) {
            return self::$dt0Cache[static::class];
        }

        return self::$dt0Cache[static::class] = Properties::make(static::class);
    }

    public function __sleep(): array
    {
        return array_keys($this->dt0Properties->toArray());
    }

    /**
     * @throws ReflectionException
     */
    public function __wakeup(): void
    {
        $this->dt0Properties = static::compile();
    }

    public static function classBasename(object|string $objectOrClass): string
    {
        return basename(
            str_replace(
                '\\',
                '/',
                static::fqn($objectOrClass),
            ),
        );
    }

    public static function fqn(object|string $objectOrClass): string
    {
        return is_string($objectOrClass) ? $objectOrClass : get_class($objectOrClass);
    }

    /**
     * @throws Dt0Exception|ReflectionException
     */
    public function setProp(string $name, mixed $value): static
    {

        if (
            ! ($prop = $this->dt0Properties->get($name))
            || $prop->property->isReadOnly()
        ) {
            throw (new Dt0Exception($prop ? "Attempt to write in read-only property $prop->name" : "Undefined property $name"))->setContext([
                'name'  => $name,
                'value' => $value,
            ]);
        }

        $this->$name = $prop->cast($value, $this);

        return $this->clearOutputCache();
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->toArray());
    }

    /**
     * @throws Dt0Exception|ReflectionException
     */
    public function offsetSet($offset, $value): void
    {
        $this->setProp($offset, $value);
    }

    public function offsetExists($offset): bool
    {
        return (bool) $this->dt0Properties->get((string) $offset);
    }

    /**
     * @throws Dt0Exception
     */
    public function offsetUnset($offset): void
    {
        throw new Dt0Exception('Cannot unset property, try set null instead');
    }

    public function offsetGet($offset): mixed
    {
        return $this->$offset ?? null;
    }

    public function clearOutputCache(): static
    {
        $this->dt0Output = [];

        return $this;
    }

    public function getProperties(): Properties
    {
        return $this->dt0Properties;
    }
}
