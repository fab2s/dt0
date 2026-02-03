<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Property;

use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Attribute\CastInterface;
use fab2s\Dt0\Caster\CasterInterface;
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Type\Types;
use fab2s\Enumerate\Enumerate;
use ReflectionAttribute;
use ReflectionException;
use ReflectionProperty;

class Property
{
    public readonly string $name;
    public readonly Types $types;
    public readonly ?CasterInterface $in;
    public readonly ?CasterInterface $out;
    public readonly ?CastInterface $cast;
    public readonly bool $isDt0;
    public readonly bool $isEnum;
    public readonly bool $needEarlyDefault;
    public readonly bool $needEarlyCast;
    protected mixed $default   = Dt0::DT0_NIL;
    protected bool $hasDefault = false;

    /**
     * @throws ReflectionException
     */
    public function __construct(public readonly ReflectionProperty $property, ?Cast $cast = null)
    {
        $this->cast        = static::resolveAttribute($this->property, CastInterface::class) ?: $cast;
        $declaringClassFqn = $this->property->getDeclaringClass()->getName();
        $this->name        = $this->property->getName();
        $this->cast?->setDeclaringFqn($declaringClassFqn)->setPropName($this->name);
        $this->in     = $this->cast?->in?->setPropName($this->name)->setDeclaringFqn($declaringClassFqn);
        $this->out    = $this->cast?->out?->setPropName($this->name)->setDeclaringFqn($declaringClassFqn);
        $this->types  = Types::make($this->property);
        $this->isDt0  = ! empty($this->types->getDt0Fqns());
        $this->isEnum = ! empty($this->types->getEnumFqns());

        foreach (['cast', 'types'] as $prop) {
            if ($this->$prop?->hasDefault) {
                $this->setDefault($this->$prop->default);
                break;
            }
        }

        if (! $this->hasDefault() && $this->types->isNullable) {
            $this->setDefault(null);
        }

        $this->needEarlyDefault = $this->property->isPromoted() && $this->hasDefault();
        $this->needEarlyCast    = $this->property->isPromoted() && ($this->in || $this->isDt0 || $this->isEnum);
    }

    /**
     * @throws ReflectionException
     */
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
     * @throws ReflectionException
     */
    public function cast(mixed $value, array|Dt0|null $input): mixed
    {
        if ($this->in) {
            // gives the opportunity to enforce more
            // things on objects in the caster
            // eg timezone for Datetime
            return $this->in->cast($value, $input);
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
                if ($case = Enumerate::tryFromAny($enumFqn, $value)) {
                    $value = $case;
                    break;
                }
            }
        }

        return $value;
    }

    /**
     * @template T
     *
     * @param class-string<T> $name  Name of an attribute class
     * @param int             $flags Criteria by which the attribute is searched.
     *
     * @return object<T>|null
     *
     * @throws ReflectionException
     */
    public static function resolveAttribute(ReflectionProperty $property, string $name, int $flags = ReflectionAttribute::IS_INSTANCEOF): ?object
    {
        $attribute = $property->getAttributes($name, $flags)[0] ?? null;
        $propName  = $property->getName();
        $parent    = $property->getDeclaringClass()
            ->getParentClass()
        ;

        while (
            ! $attribute
            && $parent
            && $parent->getName() !== Dt0::class
        ) {
            if ($parent->hasProperty($propName)) {
                $attribute = $parent->getProperty($propName)
                    ->getAttributes($name, $flags)[0]
                    ?? null
                ;
            }

            $parent = $parent->getParentClass();
        }

        return $attribute?->newInstance();
    }
}
