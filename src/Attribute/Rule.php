<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Rule
{
    public function __construct(
        public readonly mixed $rule,
        public readonly ?string $propName = null,
    ) {
    }

    public static function make(
        mixed $rule,
        ?string $propName = null,
    ): static {
        return new static(
            rule: $rule,
            propName: $propName,
        );
    }
}
