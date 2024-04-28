<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Caster;

use fab2s\Dt0\Dt0;
use fab2s\Dt0\Exception\CasterException;
use fab2s\Dt0\Exception\Dt0Exception;
use fab2s\Dt0\Property\Property;
use JsonException;
use UnitEnum;

class ArrayOfCaster implements CasterInterface
{
    public readonly ArrayType|ScalarType|string $logicalType;
    protected ?ScalarCaster $scalarCaster;

    /**
     * @throws CasterException
     */
    public function __construct(
        /** @var class-string<Dt0|UnitEnum>|ScalarType|string */
        public readonly ScalarType|string $type,
    ) {
        if (is_string($type)) {
            $logicalType = match (true) {
                is_subclass_of($type, Dt0::class)      => ArrayType::DT0,
                is_subclass_of($type, UnitEnum::class) => ArrayType::ENUM,
                default                                => ScalarType::tryFrom($type),
            };
        } else {
            $logicalType = $type;
        }

        if (! $logicalType) {
            throw new CasterException('[' . Dt0::classBasename(static::class) . "] $type is not a supported type");
        }

        $this->logicalType  = $logicalType;
        $this->scalarCaster = $this->logicalType instanceof ScalarType ? new ScalarCaster($this->logicalType) : null;
    }

    /**
     * @throws Dt0Exception
     * @throws JsonException
     */
    public function cast(mixed $value): ?array
    {
        if (! is_iterable($value)) {
            return null;
        }

        $result = [];
        foreach ($value as $item) {
            $result[] = match ($this->logicalType) {
                ArrayType::DT0  => $this->type::from($item),
                ArrayType::ENUM => Property::enumFrom($this->type, $item),
                default         => $this->scalarCaster->cast($item) ?? throw (new CasterException('Could not cast array item to scalar type ' . $this->logicalType->value))->setContext([
                    'item' => $item,
                ]),
            };
        }

        return $result;
    }
}
