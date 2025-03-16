<?php

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
abstract class WithAbstract implements WithInterface
{
    use HasDeclaringFqn;

    /**
     * @var array<string, WithProp>
     */
    protected array $withs = [];

    public function hasWith(string $name): bool
    {
        return isset($this->withs[$name]);
    }

    public function getWith(string $name): ?WithProp
    {
        return $this->withs[$name] ?? null;
    }

    public function getWiths(): array
    {
        return $this->withs;
    }
}
