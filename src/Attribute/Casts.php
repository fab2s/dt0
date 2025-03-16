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
class Casts extends CastsAbstract
{
    public function __construct(
        Cast ...$casters,
    ) {
        foreach ($casters as $name => $caster) {
            if (is_int($name)) {
                if (! $caster->getPropName()) {
                    continue;
                }

                $name = $caster->getPropName();
            }

            $this->casts[$name] = $caster->setPropName($name);
        }
    }
}
