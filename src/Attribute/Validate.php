<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Attribute;

use Attribute;
use fab2s\Dt0\Exception\AttributeException;
use fab2s\Dt0\Validator\ValidatorInterface;

#[Attribute(Attribute::TARGET_CLASS)]
class Validate
{
    public readonly ValidatorInterface $validator;

    /**
     * @throws AttributeException
     */
    public function __construct(
        /** @var ValidatorInterface|class-string<ValidatorInterface> $validator */
        ValidatorInterface|string $validator,
        public readonly ?Rules $rules = null,
    ) {
        $this->validator = match (true) {
            $validator instanceof ValidatorInterface              => $validator,
            is_subclass_of($validator, ValidatorInterface::class) => new $validator,
            default                                               => throw new AttributeException('[Validate] Validator must implement ValidatorInterface'),
        };
    }
}
