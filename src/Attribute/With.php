<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class With extends WithAbstract
{
    public function __construct(
        WithProp|string ...$withCasts,
    ) {
        foreach ($withCasts as $name => $withCast) {
            if (is_int($name)) {
                $name = is_string($withCast) ? $withCast : $withCast->getPropName();
                if (! $name) {
                    continue;
                }
            }

            $cast = is_object($withCast) ? $withCast : WithProp::make(name: $name, getter: $withCast);
            if (! $cast->getPropName()) {
                $cast->setPropName($name);
            }

            $this->withs[$name] = $cast;
        }
    }
}
