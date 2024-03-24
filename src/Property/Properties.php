<?php

/*
 * This file is part of fab2s/Dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/Dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Property;

use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Attribute\Casts;
use fab2s\Dt0\Dt0;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use ReflectionProperty;

class Properties
{
    /**
     * @var array<string, Property>
     */
    protected array $properties = [];

    /**
     * @var array <string, string>
     */
    public readonly array $constructorParameters;
    protected array $renameFrom = [];
    protected array $renameTo   = [];

    /**
     * @throws ReflectionException
     */
    public function __construct(public readonly object|string $objectOrClass)
    {
        $reflection            = new ReflectionClass($this->objectOrClass);
        $constructorParameters = [];
        $constructor           = $reflection->getConstructor();
        if ($constructor->getDeclaringClass()->getName() !== Dt0::class) {
            foreach ($constructor->getParameters() as $parameter) {
                $constructorParameters[$parameter->getName()] = $parameter;
            }
        }

        $this->constructorParameters = $constructorParameters;

        $reflectionProperties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
        $classCaster          = $reflection->getAttributes(Casts::class)[0] ?? null;
        /** @var ?Casts $classCast */
        $classCast = $classCaster ? $classCaster->newInstance() : null;
        foreach ($reflectionProperties as $reflectionProperty) {
            $name = $reflectionProperty->getName();
            if ($classCast?->hasCast($name)) {
                $this->registerProp($reflectionProperty, $classCast->getCast($name));

                continue;
            }

            $this->registerProp($reflectionProperty);
        }
    }

    /**
     * @throws ReflectionException
     */
    public static function make(object|string $objectOrClass): static
    {
        return new static($objectOrClass);
    }

    protected function registerProp(ReflectionProperty $property, ?Cast $cast = null): static
    {
        $name = $property->getName();
        $prop = Property::make($property, $cast);
        if (! $prop->hasDefault()) {
            if ($param = $this->constructorParameters[$name] ?? null) {
                /** @var ReflectionParameter $param */
                //@todo check $param->isPromoted() ?
                if ($param->isDefaultValueAvailable()) {
                    $prop->setDefault($param->getDefaultValue());
                }
            }
        }

        $this->properties[$name] = $prop;

        if ($prop->cast?->renameFrom) {
            if (is_array($prop->cast->renameFrom)) {
                foreach ($prop->cast->renameFrom as $from) {
                    $this->renameFrom[$from] = $name;
                }
            } else {
                $this->renameFrom[$prop->cast->renameFrom] = $name;
            }
        }

        if ($prop->cast?->renameTo) {
            $this->renameTo[$name]                   = $prop->cast->renameTo;
            $this->renameFrom[$prop->cast->renameTo] = $name;
        }

        return $this;
    }

    public function push(Property|array ...$properties): static
    {
        foreach ($properties as $property) {
            $property                          = $property instanceof Property ? $property : Property::make(...$property);
            $this->properties[$property->name] = $property;
        }

        return $this;
    }

    public function get(string $name): ?Property
    {
        return $this->properties[$name] ?? null;
    }

    public function toArray(): array
    {
        return $this->properties;
    }

    public function getRenameFrom(): array
    {
        return $this->renameFrom;
    }

    public function getRenameTo(): array
    {
        return $this->renameTo;
    }
}
