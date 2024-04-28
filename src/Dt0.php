<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0;

use fab2s\Dt0\Exception\Dt0Exception;
use fab2s\Dt0\Property\Properties;
use fab2s\Dt0\Property\Property;
use JsonException;
use JsonSerializable;
use Stringable;
use Throwable;
use UnitEnum;

abstract class Dt0 implements JsonSerializable, Stringable
{
    public const DT0_NIL = "\0";
    protected Properties $dt0Properties;
    protected array $dt0Output       = [];
    protected static array $dt0Cache = [];

    /**
     * @throws JsonException|Dt0Exception
     */
    public function __construct(mixed ...$args)
    {
        $this->dt0Properties = static::compile();
        $args                = static::initializeRenameFrom($this->dt0Properties, $args);
        foreach ($this->dt0Properties->toArray() as $name => $property) {
            if (! $property->property->isInitialized($this)) {
                if (static::initializeValue($property, $args, $value)) {
                    $property->property->setValue($this, $value);
                } else {
                    throw (new Dt0Exception("Missing required property $name in " . static::class))
                        ->setContext([
                            'input' => $args,
                        ])
                    ;
                }
            }
        }
    }

    /**
     * @throws JsonException|Dt0Exception
     */
    public static function make(mixed ...$args): static
    {
        $properties = static::compile();
        $args       = static::initializeRenameFrom($properties, $args);

        foreach ($properties->toArray() as $name => $property) {
            if (static::initializeValue($property, $args, $value)) {
                $args[$name] = $value;
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
     * @throws JsonException|Dt0Exception
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
     * @throws JsonException|Dt0Exception
     */
    public function clone(): static
    {
        return static::fromArray($this->toArray());
    }

    /**
     * @throws JsonException|Dt0Exception
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
            return $this->dt0Output[Format::ARRAY->value];
        }

        $result = [];
        foreach ($this->dt0Properties->toArray() as $name => $property) {
            $result[$name] = $this->$name;
        }

        return $this->dt0Output[Format::ARRAY->value] = $result;
    }

    public function jsonSerialize(): array
    {
        if (isset($this->dt0Output[Format::JSON_SERIALISED->value])) {
            return $this->dt0Output[Format::JSON_SERIALISED->value];
        }

        $result = $this->toArray();
        foreach ($result as $name => $value) {
            $property = $this->dt0Properties->get($name);
            if ($property?->out) {
                $value = $property->out->cast($value);
            }

            $key          = $this->dt0Properties->getToName($name);
            $result[$key] = match (true) {
                $value instanceof JsonSerializable => $value->jsonSerialize(),
                $value instanceof UnitEnum         => $value->value ?? $value->name,
                default                            => $value,
            };

            if ($key !== $name) {
                unset($result[$name]);
            }

        }

        return $this->dt0Output[Format::JSON_SERIALISED->value] = $result;
    }

    /**
     * @throws JsonException
     */
    public function toJson(int $flags = 0, int $depth = 512): string
    {
        return $this->dt0Output[Format::JSON->value] ??= json_encode($this, JSON_THROW_ON_ERROR & $flags, $depth);
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
     * @throws JsonException|Dt0Exception
     */
    public static function fromArray(array $input): static
    {
        return static::make(...$input);
    }

    /**
     * @throws JsonException|Dt0Exception
     */
    public static function fromJson(string $json, int $depth = 512): static
    {
        return static::make(...json_decode($json, true, $depth, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException|Dt0Exception
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
     * @throws JsonException|Dt0Exception
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

    /**
     * @throws JsonException|Dt0Exception
     */
    protected static function initializeValue(Property $property, array $input, mixed &$value = null): bool
    {
        $hasValue = false;
        if (array_key_exists($property->name, $input)) {
            $value    = $input[$property->name];
            $hasValue = true;
        } elseif ($property->hasDefault()) {
            $value    = $property->getDefault();
            $hasValue = true;
        }

        if ($hasValue) {
            $value = $property->cast($value);
        }

        return $hasValue;
    }

    protected static function initializeRenameFrom(Properties $properties, array $parameters): array
    {
        foreach ($properties->getRenameFrom() as $from => $to) {
            if (
                ! array_key_exists($to, $parameters)
                && array_key_exists($from, $parameters)
            ) {
                $parameters[$to] = $parameters[$from];
                unset($parameters[$from]);
            }
        }

        return $parameters;
    }

    final protected static function compile(): Properties
    {
        if (isset(self::$dt0Cache[static::class])) {
            return self::$dt0Cache[static::class];
        }

        return self::$dt0Cache[static::class] = new Properties(static::class);
    }

    public function __sleep(): array
    {
        return array_keys($this->dt0Properties->toArray());
    }

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
                is_string($objectOrClass) ? $objectOrClass : get_class($objectOrClass),
            ),
        );
    }
}
