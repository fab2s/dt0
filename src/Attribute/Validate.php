<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Attribute;

use Attribute;
use fab2s\Dt0\Validator\ValidatorInterface;

#[Attribute(Attribute::TARGET_CLASS)]
class Validate
{
    public readonly ValidatorInterface $validator;

    public function __construct(
        /** @var ValidatorInterface|class-string<ValidatorInterface> $validator */
        ValidatorInterface|string $validator,
        public readonly ?Rules $rules = null,
    ) {
        $this->validator = $validator instanceof ValidatorInterface ? $validator : new $validator;
    }
}
