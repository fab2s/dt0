<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Tests\Artifacts;

use fab2s\Dt0\Attribute\Rule;
use fab2s\Dt0\Attribute\Rules;
use fab2s\Dt0\Attribute\Validate;
use fab2s\Dt0\Dt0;

#[Validate(
    validator: NoOpValidator::class,
    rules: new Rules(
        fromValidate: new Rule('rule1'),
    ),
)]
#[Rules(
    fromRules: new Rule('rule2'),
)]
class DummyValidatedDt0 extends Dt0
{
    public readonly string $fromValidate;
    public readonly string $fromRules;

    #[Rule('rule3')]
    public readonly string $fromRule;
}
