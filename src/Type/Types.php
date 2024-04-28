<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Type;

use BackedEnum;
use fab2s\Dt0\Dt0;
use LogicException;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionUnionType;
use UnitEnum;

class Types
{
    /**
     * @var array<string, Type>
     */
    protected array $types = [];
    public readonly bool $isReadOnly;
    public readonly bool $hasDefault;
    public readonly bool $isDefault;
    public readonly mixed $default;
    public readonly bool $isNullable;
    public readonly bool $isUnion;
    public readonly bool $isIntersection;

    /**
     * @var array<class-string<UnitEnum|BackedEnum>>
     */
    protected array $enumFqns = [];

    /**
     * @var array<class-string<Dt0>>
     */
    protected array $dt0Fqns = [];

    public function __construct(public readonly ReflectionProperty|ReflectionParameter $property)
    {
        [
            'isReadOnly'     => $this->isReadOnly,
            'hasDefault'     => $this->hasDefault,
            'isDefault'      => $this->isDefault,
            'default'        => $this->default,
            'isNullable'     => $this->isNullable,
            'isUnion'        => $this->isUnion,
            'isIntersection' => $this->isIntersection
        ] = $this->registerType();
    }

    public static function make(ReflectionProperty|ReflectionParameter $property): static
    {
        return new static($property);
    }

    protected function registerType(): array
    {
        $isNullable = $isUnion = $isIntersection = false;
        foreach ($this->getTypes() as $type) {
            $this->types[$type->name] = $type;
            $isNullable               = $isNullable     || $type->allowsNull;
            $isUnion                  = $isUnion        || $type->isUnion;
            $isIntersection           = $isIntersection || $type->isIntersection;

            if (is_subclass_of($type->name, UnitEnum::class)) {
                $this->enumFqns[$type->name] = $type->name;
            }

            if (is_subclass_of($type->name, Dt0::class)) {
                $this->dt0Fqns[$type->name] = $type->name;
            }
        }

        return [
            'isReadOnly'     => $this->property->isReadOnly(),
            'hasDefault'     => $this->property->hasDefaultValue(),
            'isDefault'      => $this->property->isDefault(),
            'default'        => $this->property->getDefaultValue(),
            'isNullable'     => $isNullable,
            'isUnion'        => $isUnion,
            'isIntersection' => $isIntersection,
        ];
    }

    /**
     * @return array<int, Type>
     */
    protected function getTypes(): array
    {
        $type               = $this->property->getType();
        $types              = [];
        $isIntersectionType = false;
        switch (true) {
            case $type instanceof ReflectionNamedType:
                $types[] = Type::make(
                    name: $type->getName(),
                    allowsNull: $type->allowsNull(),
                    isBuiltin: $type->isBuiltin(),
                );
                break;
            case $type instanceof ReflectionIntersectionType:
                $isIntersectionType = true;
            case $type instanceof ReflectionUnionType:
                foreach ($type->getTypes() as $multiType) {
                    $types[] = Type::make(
                        name: $multiType->getName(),
                        allowsNull: $multiType->allowsNull(),
                        isBuiltin: $multiType->isBuiltin(),
                        isUnion: ! $isIntersectionType,
                        isIntersection: $isIntersectionType,
                    );
                }
                break;
            default:
                if ($type === null) {
                    $types[] = Type::make(
                        name: 'mixed',
                        allowsNull: true,
                        isBuiltin: true,
                    );
                    break;
                }

                // @codeCoverageIgnoreStart
                // is there even a way to reach here ?
                throw new LogicException('Received unknown type from ReflectionProperty::getType');
                // @codeCoverageIgnoreEnd
        }

        return $types;
    }

    public function get(string $name): ?Type
    {
        return $this->types[$name] ?? null;
    }

    public function has(string $name): bool
    {
        return isset($this->types[$name]);
    }

    public function toArray(): array
    {
        return $this->types;
    }

    /**
     * @return array<class-string<UnitEnum|BackedEnum>>
     */
    public function getEnumFqns(): array
    {
        return $this->enumFqns;
    }

    /**
     * @return array<class-string<Dt0>>
     */
    public function getDt0Fqns(): array
    {
        return $this->dt0Fqns;
    }
}
