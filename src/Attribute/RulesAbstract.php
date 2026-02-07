<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Attribute;

use Attribute;
use fab2s\Dt0\Concern\HasDeclaringFqn;

#[Attribute(Attribute::TARGET_CLASS)]
abstract class RulesAbstract implements RulesInterface
{
    use HasDeclaringFqn;

    /**
     * @var array<string, Rule>
     */
    protected array $rules = [];

    public function hasRule($name): bool
    {
        return isset($this->rules[$name]);
    }

    public function getRule($name): ?Rule
    {
        return $this->rules[$name] ?? null;
    }
}
