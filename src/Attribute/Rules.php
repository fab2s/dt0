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
class Rules extends RulesAbstract
{
    public function __construct(
        Rule ...$rules,
    ) {
        foreach ($rules as $name => $rule) {
            if (is_int($name)) {
                if (! $rule->getPropName()) {
                    continue;
                }

                $name = $rule->getPropName();
            }

            $this->rules[$name] = $rule->setPropName($name);
        }
    }
}
