<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Property;

use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Attribute\CastsInterface;
use fab2s\Dt0\Attribute\Rule;
use fab2s\Dt0\Attribute\RuleInterface;
use fab2s\Dt0\Attribute\RulesInterface;
use fab2s\Dt0\Attribute\ValidateInterface;
use fab2s\Dt0\Attribute\WithInterface;
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Validator\ValidatorInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use ReflectionProperty;

class Properties
{
    /**
     * @var array <string, ReflectionParameter>
     */
    public readonly array $constructorParameters;
    public readonly ?ValidatorInterface $validator;
    public readonly ?CastsInterface $casts;
    public readonly ?WithInterface $with;

    /**
     * @var class-string<$objectOrClass>
     */
    public readonly string $name;

    /**
     * @var array<string, Property>
     */
    protected array $properties = [];

    /**
     * @var array<string, Property>
     */
    protected array $earlyInit = [];

    /**
     * @var array<string, string>
     */
    protected ?array $names;

    /**
     * @var array<string, string>
     */
    protected array $renameFrom = [];

    /**
     * @var array<string, string>
     */
    protected array $renameTo = [];

    /**
     * @throws ReflectionException
     */
    public function __construct(public readonly object|string $objectOrClass)
    {
        $reflection                  = new ReflectionClass($this->objectOrClass);
        $this->name                  = $reflection->getName();
        $this->constructorParameters = $this->getConstructorParameters($reflection);
        $this->casts                 = $this->getCasts($reflection);
        $this->with                  = $this->getWith($reflection);
        $rules                       = $this->getRules($reflection);
        $validate                    = $this->getValidate($reflection);
        $validatorRules              = $validate?->rules?->setDeclaringFqn($reflection->getName());
        $this->validator             = $validate?->validator?->setDeclaringFqn($reflection->getName());
        $reflectionProperties        = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($reflectionProperties as $reflectionProperty) {
            $name = $reflectionProperty->getName();
            $this->registerProp(
                $reflectionProperty,
                $this->casts?->hasCast($name)
                    ? $this->casts->getCast($name)
                        ->setDeclaringFqn($reflection->getName())
                        ->setPropName($name)
                    : null,
            );

            if (! $this->validator) {
                continue;
            }

            $rule = Property::resolveAttribute($reflectionProperty, RuleInterface::class)
                ?: (
                    $rules?->hasRule($name)
                    ? $rules->getRule($name)
                        ->setDeclaringFqn($reflection->getName())
                        ->setPropName($name)
                    : (
                        $validatorRules?->hasRule($name)
                        ? $validatorRules->getRule($name)
                            ->setDeclaringFqn($reflection->getName())
                            ->setPropName($name)
                        : null
                    )
                );

            if ($rule) {
                /** @var Rule $rule */
                $this->validator->addRule($name, $rule);
            }
        }
    }

    protected function getConstructorParameters(ReflectionClass $reflection): array
    {
        $constructorParameters = [];
        $constructor           = $reflection->getConstructor();
        if ($constructor->getDeclaringClass()->getName() !== Dt0::class) {
            foreach ($constructor->getParameters() as $parameter) {
                $constructorParameters[$parameter->getName()] = $parameter;
            }
        }

        return $constructorParameters;
    }

    protected function getCasts(ReflectionClass $reflection): ?CastsInterface
    {
        return $this->getClassAttribute($reflection, CastsInterface::class);
    }

    protected function getWith(ReflectionClass $reflection): ?WithInterface
    {
        return $this->getClassAttribute($reflection, WithInterface::class);
    }

    protected function getRules(ReflectionClass $reflection): ?RulesInterface
    {
        return $this->getClassAttribute($reflection, RulesInterface::class);
    }

    protected function getValidate(ReflectionClass $reflection): ?ValidateInterface
    {
        return $this->getClassAttribute($reflection, ValidateInterface::class);
    }

    /**
     * @param class-string<CastsInterface|WithInterface|ValidateInterface> $attributeFqn
     *
     * @return CastsInterface|WithInterface|ValidateInterface|RulesInterface|null.
     */
    protected function getClassAttribute(ReflectionClass $reflection, string $attributeFqn): CastsInterface|WithInterface|ValidateInterface|RulesInterface|null
    {
        $classAttribute = $reflection->getAttributes($attributeFqn, ReflectionAttribute::IS_INSTANCEOF)[0] ?? null;
        $parent         = $reflection->getParentClass();

        while (
            ! $classAttribute
            && $parent
            && $parent->getName() !== Dt0::class
        ) {
            $classAttribute = $parent->getAttributes($attributeFqn, ReflectionAttribute::IS_INSTANCEOF)[0] ?? null;
            $parent         = $parent->getParentClass();
        }

        return $classAttribute?->newInstance()?->setDeclaringFqn($reflection->getName());
    }

    /**
     * @throws ReflectionException
     */
    public static function make(object|string $objectOrClass): static
    {
        return new static($objectOrClass);
    }

    /**
     * @throws ReflectionException
     */
    protected function registerProp(ReflectionProperty $property, ?Cast $cast = null): static
    {
        $name = $property->getName();
        $prop = Property::make($property, $cast);

        if (! $prop->hasDefault()) {
            if ($param = $this->constructorParameters[$name] ?? null) {
                /** @var ReflectionParameter $param */
                // @todo check $param->isPromoted() ?
                if ($param->isDefaultValueAvailable()) {
                    $prop->setDefault($param->getDefaultValue());
                }
            }
        }

        $this->properties[$name] = $prop;
        if ($prop->needEarlyCast || $prop->needEarlyDefault) {
            $this->earlyInit[$name] = $prop;
        }

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

    public function earlyInits(): array
    {
        return $this->earlyInit;
    }

    public function toNames(): array
    {
        return $this->names ??= array_combine(array_keys($this->properties), array_keys($this->properties));
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
