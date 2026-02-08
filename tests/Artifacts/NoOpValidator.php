<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace Tests\Artifacts;

use fab2s\Dt0\Attribute\Rule;
use fab2s\Dt0\Concern\HasDeclaringFqn;
use fab2s\Dt0\Validator\ValidatorInterface;

class NoOpValidator implements ValidatorInterface
{
    use HasDeclaringFqn;
    public array $rules = [];

    public function validate(array $data): array
    {
        return $data;
    }

    public function addRule(string $name, Rule $rule): static
    {
        $this->rules[$name] = $rule;

        return $this;
    }
}
