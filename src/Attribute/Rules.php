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
class Rules
{
    /**
     * @var array<string, Rule>
     */
    protected array $rules = [];

    public function __construct(
        Rule ...$rules,
    ) {
        foreach ($rules as $name => $rule) {
            if (is_int($name)) {
                if (! $rule->propName) {
                    continue;
                }

                $name = $rule->propName;
            }

            $this->rules[$name] = $rule;
        }
    }

    public function hasRule($name): bool
    {
        return isset($this->rules[$name]);
    }

    public function getRule($name): ?Rule
    {
        return $this->rules[$name] ?? null;
    }

    /**
     * @return Rule[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }
}
