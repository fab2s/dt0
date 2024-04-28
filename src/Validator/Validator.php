<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Validator;

use fab2s\Dt0\Attribute\Rule;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;

class Validator implements ValidatorInterface
{
    protected Factory $validator;
    protected array $rules = [];

    public function __construct()
    {
        $this->validator = new Factory(trans());
    }

    /**
     * @throws ValidationException
     */
    public function validate(array $data): array
    {
        return $this->validator->make($data, $this->rules)->validate();
    }

    public function addRule(string $name, Rule $rule): static
    {
        $this->rules[$name] = $rule->rule;

        return $this;
    }
}
