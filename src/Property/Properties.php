<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Property;

use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Attribute\Casts;
use fab2s\Dt0\Attribute\Rule;
use fab2s\Dt0\Attribute\Rules;
use fab2s\Dt0\Attribute\Validate;
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Validator\ValidatorInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use ReflectionProperty;

class Properties
{
    /**
     * @var array <string, string>
     */
    public readonly array $constructorParameters;
    public readonly ?ValidatorInterface $validator;

    /**
     * @var array<string, Property>
     */
    protected array $properties = [];
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
        $castsAttribute       = $reflection->getAttributes(Casts::class)[0] ?? null;
        /** @var ?Casts $casts */
        $casts = $castsAttribute?->newInstance();

        $rulesAttribute = $reflection->getAttributes(Rules::class)[0] ?? null;
        /** @var ?Rules $rules */
        $rules = $rulesAttribute?->newInstance();

        $validatorAttribute = $reflection->getAttributes(Validate::class)[0] ?? null;
        $this->validator    = $validatorAttribute?->newInstance()->validator;

        foreach ($reflectionProperties as $reflectionProperty) {
            $name = $reflectionProperty->getName();
            if ($casts?->hasCast($name)) {
                $this->registerProp($reflectionProperty, $casts->getCast($name));

                continue;
            }

            $this->registerProp($reflectionProperty);

            if (! $this->validator) {
                continue;
            }

            if ($rules?->hasRule($name)) {
                $this->validator->addRule($name, $rules->getRule($name));

                continue;
            }

            $ruleAttribute = $reflectionProperty->getAttributes(Rule::class)[0] ?? null;
            if ($rule = $ruleAttribute?->newInstance()) {
                /** @var Rule $rule */
                $this->validator->addRule($name, $rule);
            }
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

    public function getToName(string $name): string
    {
        return $this->renameTo[$name] ?? $name;
    }
}
