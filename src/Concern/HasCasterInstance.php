<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Concern;

use fab2s\Dt0\Caster\CasterInterface;
use fab2s\Dt0\Exception\AttributeException;

trait HasCasterInstance
{
    /**
     * @throws AttributeException
     */
    public function getCasterInstance(CasterInterface|string|null $caster): ?CasterInterface
    {
        return match (true) {
            is_object($caster)                              => $caster,
            is_subclass_of($caster, CasterInterface::class) => new $caster,
            $caster === null                                => null,
            default                                         => throw new AttributeException('[Cast] Cast must implement CasterInterface'),
        };
    }
}
