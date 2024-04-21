<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Property;

use BackedEnum;
use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Caster\CasterInterface;
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Exception\Dt0Exception;
use fab2s\Dt0\Type\Types;
use JsonException;
use ReflectionProperty;
use UnitEnum;

class Property
{
    public readonly string $name;
    public readonly Types $types;
    public readonly ?CasterInterface $in;
    public readonly ?CasterInterface $out;
    public readonly ?Cast $cast;
    private readonly bool $isDt0;
    private readonly bool $isEnum;
    protected mixed $default   = Dt0::DT0_NIL;
    protected bool $hasDefault = false;

    public function __construct(public readonly ReflectionProperty $property, ?Cast $cast = null)
    {
        if ($cast) {
            $this->cast = $cast;
        } else {
            $castAttribute = $this->property->getAttributes(Cast::class)[0] ?? null;
            $this->cast    = $castAttribute?->newInstance();
        }

        $this->in     = $this->cast?->in;
        $this->out    = $this->cast?->out;
        $this->name   = $this->property->getName();
        $this->types  = Types::make($this->property);
        $this->isDt0  = ! empty($this->types->getDt0Fqns());
        $this->isEnum = ! empty($this->types->getEnumFqns());

        foreach (['cast', 'types'] as $prop) {
            if ($this->$prop?->hasDefault) {
                $this->setDefault($this->$prop->default);
                break;
            }
        }
    }

    public static function make(ReflectionProperty $property, ?Cast $cast = null): static
    {
        return new static($property, $cast);
    }

    public function hasDefault(): bool
    {
        return $this->hasDefault;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }

    public function setDefault(mixed $default): static
    {
        $this->hasDefault = true;
        $this->default    = $default;

        return $this;
    }

    /**
     * @throws JsonException
     * @throws Dt0Exception
     */
    public function cast(mixed $value): mixed
    {
        if ($this->in) {
            // gives the opportunity to enforce more
            // things on objects in the caster
            // eg timezone for Datetimes
            return $this->in->cast($value);
        }

        if (is_object($value)) {
            return $value;
        }

        if ($this->isDt0 && ! empty($value)) {
            foreach ($this->types->getDt0Fqns() as $dt0Fqn) {
                /** @var Dt0 $dt0Fqn */
                if ($dt0 = $dt0Fqn::tryFrom($value)) {
                    $value = $dt0;
                    break;
                }
            }
        }

        if ($this->isEnum && (is_string($value) || is_int($value))) {
            foreach ($this->types->getEnumFqns() as $enumFqn) {
                if ($case = static::tryEnum($enumFqn, $value)) {
                    $value = $case;
                    break;
                }
            }
        }

        return $value;
    }

    public static function tryEnum(?string $enumFqn, string|int|null $value): UnitEnum|BackedEnum|null
    {
        if (! $enumFqn) {
            return null;
        }

        if (is_subclass_of($enumFqn, BackedEnum::class)) {
            return $enumFqn::tryFrom($value);
        }

        return static::tryEnumFromName($enumFqn, $value);
    }

    public static function tryEnumFromName(string $enumFqn, ?string $name): UnitEnum|BackedEnum|null
    {
        if ($name && is_subclass_of($enumFqn, UnitEnum::class)) {
            foreach ($enumFqn::cases() as $case) {
                if ($case->name === $name) {
                    return $case;
                }
            }
        }

        return null;
    }
}
